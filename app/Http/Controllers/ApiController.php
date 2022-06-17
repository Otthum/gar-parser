<?php

namespace App\Http\Controllers;

use App\Models\MunHierarchy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SearchTermsConverterService as Converter;

class ApiController extends Controller
{
    public function search(Request $req)
    {
        if ( !($search = $req->q) ) {
            return response()->json([
                'result' => false,
                'message' => 'Укажите строку для поиска'
            ], 400);
        }

        $query = <<<EOT
            select
                mh.gar_id,
                match (address_str) against (? in boolean mode) as rel
            from mun_hierarchies mh
            where
                match (address_str) against (?  in boolean mode) > 0 and
                mh.is_active = 1
            limit 200
        EOT;

        $search = preg_replace('~, *~', ' ', $search);
        $search = Converter::convert($search);

        $ids = DB::select($query, [$search, $search]);

        if (count($ids) > 0) {
            $addresses = $this->getAddressesFromIds($ids);
        }

        $results = [
            'items' => $addresses ?? [],
            'result' => true,
            'search' => $search,
        ];
        
        return response()->json($results);
    }

    protected function getAddressesFromIds(array $ids, int $limit = 10)
    {
        $idsToFetch = [];
        $biggestRel = $ids[0]->rel;
        foreach ($ids as $item) {
            if ($item->rel == $biggestRel) {
                $idsToFetch[] = $item->gar_id;
            } else {
                break;
            }
        }

        $addresses = MunHierarchy::select(
            'mun_hierarchies.gar_id',
            'address_str',
        )
            ->leftJoin('houses', 'houses.gar_id', 'mun_hierarchies.gar_id')
            ->leftJoin('addr_objs', 'addr_objs.gar_id', 'mun_hierarchies.gar_id')
            ->whereIn('mun_hierarchies.gar_id', $idsToFetch)
            ->orderByRaw(
                'cast(houses.num as signed) asc, length(houses.num) asc, cast(houses.num_1 as signed) asc'
            )
            ->limit($limit)
            ->get()
            ->toArray();

        return $addresses;
    }
}
