<?php

namespace App\Http\Controllers;

use App\Models\AddrObj;
use App\Models\Apartment;
use App\Models\House;
use App\Models\MunHierarchy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SearchTermsConverterService as Converter;

class ApiController extends Controller
{
    protected array $objectsToInclude = [
        Apartment::class => true,
        House::class => true,
        AddrObj::class => true
    ];


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
                gar_id                
            from mun_hierarchies mh
            where
                match (address_str) against (?  in boolean mode) > 0 and
                mh.is_active = 1
            limit 200
        EOT;

        $search = preg_replace('~, *~', ' ', $search);
        $terms = explode(' ', $search);

        $formattedSearch = '';
        foreach ($terms as $key => $term) {

            if (trim($term) == '') {
                continue;
            }

            $formattedSearch .= '+' . $term;
            if (array_key_last($terms) != $key) {
                $formattedSearch .= " ";
            }
            
        }

        $formattedSearch .= '*';

        $ids = DB::select($query, [$formattedSearch]);
        $ids = array_map(function ($value) {
            return $value->gar_id;
        }, $ids);

        if (count($ids) > 0) {
            $addresses = $this->getAddressesFromIds($ids);
        }

        $results = [
            'items' => $addresses ?? [],
            'result' => true,
            'search' => $formattedSearch,
        ];
        
        return response()->json($results);
    }

    protected function getAddressesFromIds(array $ids, int $limit = 10)
    {
        $addresses = MunHierarchy::select(
            'mun_hierarchies.gar_id',
            'address_str',
        )
            ->leftJoin('houses', 'houses.gar_id', 'mun_hierarchies.gar_id')
            ->leftJoin('addr_objs', 'addr_objs.gar_id', 'mun_hierarchies.gar_id')
            ->leftJoin('apartments', 'apartments.gar_id', 'mun_hierarchies.gar_id')
            ->whereIn('mun_hierarchies.gar_id', $ids)
            ->orderByRaw(
                'length(address_str) asc, address_str asc'
            )
            ->limit($limit)
            ->where(function ($q) {
                if ($this->objectsToInclude[Apartment::class])  {
                    $q->orWhereNotNull('apartments.gar_id');
                }
        
                if ($this->objectsToInclude[House::class]) {
                    $q->orWhereNotNull('houses.gar_id');
                }
        
                if ($this->objectsToInclude[AddrObj::class]) {
                    $q->orWhereNotNull('addr_objs.gar_id');
                }
            })
            ->get()
            ->toArray();

        return $addresses;
    }
}
