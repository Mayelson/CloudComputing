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
    

        //Voter with id = XXX
     function votersById($id){
        $voters = voter::find($id);
        if($voters == null){
            $number_voters = 0;
        }else {
            $number_voters = 1;
        }
      	$response = [
            'data' => $voters,
           // 'status' => 'OK',
            //'code' => 200,
            'meta' => [
            'records_on_data' => $number_voters,
            'handled_by' => $_SERVER['SERVER_ADDR']
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
              //  'status' => 'OK',
                //'code' => 200,
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

		//$sectionsCollection = new Collection($sections);
        if($sections == null){
            $number_voters = 0;
        }else{
            $number_voters = $sections->count();
        }

        $response = [
            'data' => $sections,
            //'status' => 'OK',
            //'code' => 200,
            'meta' => [
            'records_on_data' =>$number_voters,
            'handled_by' => $_SERVER['SERVER_ADDR']
            ]
        ];
		
        return $response;

    }

    function votersSections() {
        //$paginatorLength = LengthAwarePaginator::resolveCurrentPage() * 50 - 50;
		//$itemsPerPage = 50;
		//Cache::flush();
		if(!Cache::has(1)){
			$sections1 = voter::select('section', DB::raw('count(id) as total'))->groupBy('section')->get();
			//echo "db";
			Cache::put(1, $sections1, now()->addMinutes(6440));
		}
		
		//$sections2 = voter::select( DB::raw('distinct(section) as sections'))->get();
		//$sections = $sections1->add($sections2);
		
		//$sectionsCollection = new Collection($sections1);
		//$result = new LengthAwarePaginator($sectionsCollection, 5000, $itemsPerPage);
	
		//->where('id', '>', $paginatorLength)
		//->take(50)
		//Cache::flush();
		//Cache::put(1, $sections1, now()->addMinutes(60));
		/*foreach($sections1->keyBy('section') as $item){
			Cache::put($item['section'], $item['total'], now()->addMinutes(60));

			//echo ($item['total']);
		}*/
		//dd($sections1);
		//
		$response = [
            'data' => Cache::get(1),
            //'status' => 'OK',
            //'code' => 200,
            'meta' => [
				
            'records_on_data' => 5000,
            'handled_by' => $_SERVER['SERVER_ADDR']
            ]
        ];
		
        return $response;
    }

    function votersByName(Request $request ,$params) {
        echo $request;
        die();
        $type_condition = explode("=", $params)[0];
        $name = explode("=", $params)[1];
       

        switch ($type_condition) {
            case 'equal':
                $voters = voter::where('id', '>', $number)->where('name', '=', $name)->take(1000)->get();
                break;
            case 'start':
                $voters = voter::where('id', '>', $number)->where('name', 'like', $name.'%')->take(1000)->get();
                echo "oi";
                break;
            case 'include':
                $voters = voter::where('id', '>', $number)->where('name', 'like', $name)->take(1000)->get();
                break;
            default:
                return response("",404);
                break;
        }

        if($voters == null){
            $number_voters = 0;
        }else{
            $number_voters = $voters->count();
        }
        
        //$votersCollection = new Collection($voters);
        $response = [
            'data' => $voters,
            //'status' => 'OK',
            //'code' => 200,
            'meta' => [
				
            'records_on_data' => $number_voters,
            'handled_by' => $_SERVER['SERVER_ADDR']
            ],
        ];
        return $response;
    }

    function voter () {
    	//$paginatedSearchResults = voter::paginate(100);
    	//var_dump($voters);
		   //die();
			//Get current page form url e.g. &page=6
			$response = [];
		$currentPage = LengthAwarePaginator::resolveCurrentPage();
			
		$number = $currentPage*100 - 100;
			
		$items = voter::where('id','>',$number)->take(100)->get();;
		

		//Create a new Laravel collection from the array data
		$collection = new Collection($items);

		//Define how many items we want to be visible in each page
		$perPage = 100;

		//Slice the collection to get the items to display in current page
		//$currentPageSearchResults = $collection->slice($currentPage * $perPage, $perPage)->all();

		//Create our paginator and pass it to the view
		//$paginatedSearchResults = new LengthAwarePaginator($collection, 10000000, $perPage);
		//$paginatedSearchResults
		//echo $json;
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
		//var_dump($id);
		$response ['meta'] ['handled_by'] = $_SERVER['SERVER_ADDR'];
		$voter = Voter::find($id);
		//echo ($voter);
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

