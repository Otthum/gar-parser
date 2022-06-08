<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            select mh.gar_id, address_str, h.num, match (address_str) against (?) as rel
            from mun_hierarchies mh
            left join houses h on h.gar_id = mh.gar_id
            where
                match (address_str) against (?) > 0 and
                mh.is_active = 1
        EOT;

        $search = preg_replace('~, *~', ' ', $search);
        $items = explode(' ', $search);

        $results = DB::select($query, [$search, $search]);
        

        dump($results);
    }
}
