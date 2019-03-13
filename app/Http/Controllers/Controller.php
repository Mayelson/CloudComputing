<?php

namespace App\Http\Controllers;
use App\Voter as voter;


use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    function votersSections() {
        $paginatorLength = LengthAwarePaginator::resolveCurrentPage() * 50 - 50;
        $itemsPerPage = 50;
        $sections = voter::select('section', DB::raw('count(*) as total'))->where('id', '>', $paginatorLength)->groupBy('section')->take(50)->get();
        $sectionsCollection = new Collection($sections);
        $result = new LengthAwarePaginator($sectionsCollection, 10000000, $itemsPerPage);

        return $result;
    }

    function votersByName($params) {
        $type_condition = explode("=", $params)[0];
        $name = explode("=", $params)[1];
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $number = $currentPage * 1000 - 1000;
        $itemsPerPage = 1000;

        switch ($type_condition) {
            case 'equal':
                $voters = voter::where('id', '>', $number)->where('name', '=', $name)->take(100)->get();
                break;
            case 'start':
                $voters = voter::where('id', '>', $number)->where('name', 'like', $name.'%')->take(100)->get();
                break;
            case 'include':
                $voters = voter::where('id', '>', $number)->where('name', 'like', $name)->take(100)->get();
            default:
                return response("",404);
                break;
        }
        
        $votersCollection = new Collection($voters);
        $response = [
            'body' => $votersCollection,
            'status' => 'OK',
            'code' => 200,
            'meta' => [

            'current_page' => $currentPage,
            'total' => 10000000,
            'next_page' => '/?page='.(String)($currentPage+1),
            'prev_page' => '/?page='.(String)($currentPage-1),
            'item_per_page' => $itemsPerPage,
            'handled_by' => $_SERVER['SERVER_ADDR']
            ],
        ];
        return $response;
    }

}