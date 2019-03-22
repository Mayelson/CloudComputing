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
use Illuminate\Support\Facades\Cache;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
     function votersById($id){
        $voters = voter::find($id);
        if($voters == null){
            $number_voters = 0;
        }else {
            $number_voters = 1;
        }
      	$response = [
            'meta' => [
            'records_on_data' => $number_voters,
            'handled_by' => $_SERVER['SERVER_ADDR'],
            'data' => $voters
            ]
        ];
		
        return $response;

    }
    
    function voterNumber($id) {
            $voter = voter::where('voter_number','=',$id)->get();
            if($voter == null){
                $number_voters = 0;
            }else {
                $number_voters = $voter->count();
            }
            $response = [
                'data' => $voter,
                'meta' => [
                'records_on_data' => $number_voters,
                'handled_by' => $_SERVER['SERVER_ADDR']
                ]
            ];
            return $response;
    }


      //Voters that are registered on the section XXX. Only returns the first 1000 voters.
    function votersBySection($id){
        $sections = voter::where('section', '=', $id)->take(1000)->get();
        if($sections == null){
            $number_voters = 0;
        }else{
            $number_voters = $sections->count();
        }

        $response = [
            'meta' => [
            'records_on_data' =>$number_voters,
            'handled_by' => $_SERVER['SERVER_ADDR'],
            'data' => $sections
            ]
        ];
		
        return $response;
    }

    function votersSections() {
		if(!Cache::has(1)){
			$sections1 = voter::select('section', DB::raw('count(id) as total'))->groupBy('section')->get();
			Cache::put(1, $sections1, now()->addMinutes(6440));
		}
		
		$response = [
            'meta' => [
            'records_on_data' => 5000,
            'handled_by' => $_SERVER['SERVER_ADDR'],
            'data' => Cache::get(1)
            ]
        ];
		
        return $response;
    }


    function votersByName(Request $params) {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $number = $currentPage * 1000 - 1000;
        $itemsPerPage = 1000;
    	if($params->query('equal') != null){
    		$voters = voter::where('id', '>', $number)->where('name', '=', $params->query('equal'))->take(1000)->get();
    	} elseif ($params->query('start') != null) {
    		$voters = voter::where('id', '>', $number)->where('name', 'like', $params->query('start').'%')->take(1000)->get();
    	} elseif ($params->query('include') != null) {
    		$voters = voter::where('id', '>', $number)->where('name', 'like', '%'.$params->query('include').'%')->take(1000)->get();
    	} else {
    		return response("",404);
    	}
        if ($voters == null) {
            $number_voters = 0;
        } else {
            $number_voters = count($voters);
        }
        $response = [
            'meta' => [				
                'records_on_data' => $number_voters,
                'handled_by' => $_SERVER['SERVER_ADDR']
            ],
            'data' => $voters
        ];
        return $response;
    }

    function voter () {
		//Get current page form url e.g. &page=6
		$response = [];
		$currentPage = LengthAwarePaginator::resolveCurrentPage();
			
		$number = $currentPage*100 - 100;
			
		$items = voter::where('id','>',$number)->take(100)->get();;
		
		//Create a new Laravel collection from the array data
		$collection = new Collection($items);

		//Define how many items we want to be visible in each page
		$perPage = 100;
		$response ['data']  = $collection;
		$response ['meta'] ['current_page'] =  $currentPage;
		$response ['meta'] ['total'] = 10000000 ;
		$response ['meta'] ['next_page'] =  '/?page='.(String)($currentPage+1);
		$response ['meta'] ['prev_page'] = '/?page='.(String)($currentPage-1);
		$response ['meta'] ['records_on_data'] =  $perPage;
		$response ['meta'] ['handled_by'] = $_SERVER['SERVER_ADDR'];
		
		return $response;
	}

	function vote (Request $request, $id) {
		$response ['meta'] ['handled_by'] = $_SERVER['SERVER_ADDR'];
		$voter = Voter::find($id);
		try {
			if($voter->has_voted == 1){
				$response ['msg'] = "Voter has already voted";

				return response($response,409);
			}else{
				$voter->has_voted = 1;
				$voter->save();
				
				return response($response,200);
			}
		} catch (Exception $e) {
			$response = "Something went wrong when processing vote";
			return response($response,409);
		}
		
	}

	function voteReset () {
		Voter::where('has_voted', 1)->update(['has_voted' => 0]);
		return response("",200);
	}
}

