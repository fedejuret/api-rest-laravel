<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Category;

class CategoryController extends Controller
{
	public function __construct(){
		$this->middleware('api.auth', ['except' => ['index', 'show']]);
	}
    public function index(){
    	$categories = Category::all();

    	return response()->json([
    		'code' => 200,
    		'status' => 'success',
    		'categories' => $categories
    	]);
    }

    public function show($id){
    	$category = Category::find($id);


    	if(is_object($category)){
    		$data = array(
    			'code' => 200,
	    		'status' => 'success',
	    		'category' => $category
    		);
    	}else{
    		$data = array(
    			'code' => 404,
    			'status' => 'error',
    			'message' => 'Category not found'
    		);
    	}

    	return response()->json($data, $data['code']);
    }

    public function store(Request $request){

    	$json = $request->input('json', null);
    	$params_array = json_decode($json, true);
    	// Get data post


    	if($params_array){
    		$validate = \Validator::make($params_array, [
	    		'name' => 'required'
	    	]);
	    	// Validate datos


	    	if($validate->fails()){
	    		$data = array(
	    			'code' => 400, 
	    			'status' => 'error',
	    			'message' => 'No se ha guardado la categoria'
	    		);
	    	}else{
	    		$category = new Category();
	    		$category->name = $params_array['name'];
	    		$category->save();

	    		$data = array(
	    			'code' => 200, 
	    			'status' => 'success',
	    			'message' => 'La categoria fue guardad',
	    			'category' => $category
	    		);
	    	}
    	}else{
    		$data = array(
	    			'code' => 404, 
	    			'status' => 'error',
	    			'message' => 'Error al crear la categoria'
	    		);
    	}
    	// Save category

    	return response()->json($data, $data['code']);
    	// Send result
    }

    public function update(Request $request, $id){
    	// Get url params

    	$json = $request->input('json', null);
    	$params_array = json_decode($json, true);
    	// Validate data
    	

    	if(!empty($params_array)){
    		$validate = \Validator::make($params_array, [
    			'name' => 'required'
    		]);

    		// Remover cosas que no quiero actualizar
    		unset($params_array['id']);
    		unset($params_array['created_at']);

    		// Actualizar la categoria
    		$category = Category::where('id', $id)->update($params_array);
			$data = array(
    			'code' => 200, 
    			'status' => 'success',
    			'message' => 'Categoria actualizada con exito',
    			'category' => $params_array
    		);

    	}else{

    		$data = array(
    			'code' => 404, 
    			'status' => 'error',
    			'message' => 'Error al actualizar la categoria'
    		);
    	}
    	

    	// Remove not updatable params

    	// Update 

    	return response()->json($data, $data['code']);
    }
}
