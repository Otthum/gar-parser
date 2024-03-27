<?php

namespace App\Http\Controllers;

use App\Services\Elastic\ElasticSearchService;
use Illuminate\Http\Request;

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

        $service = new ElasticSearchService();
        $res = $service->searchAddress(trim($search), $req->filters ?? [], $req->page);

        if ($res !== null) {
            return response()->json($res);    
        }
        
        return response()->json(['sucess' => false]);
    }
}
