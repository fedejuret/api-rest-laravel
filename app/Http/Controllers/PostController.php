<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Post;
use App\Helpers\JwtAuth;


class PostController extends Controller
{
    public function __construct(){
		$this->middleware('api.auth', ['except' => 
			['index', 'show', 
			'getImage', 'getPostsByCategory', 'getPostsByUser']]);
	}

	public function index(){
		$posts = Post::all()->load('category');

		return response()->json([
			'code' => 200,
			'status' => 'success',
			'posts' => $posts
		], 200);
	}

	public function show($id){
								// Load se usa para popular
		$post = Post::find($id)->load('category')
							   ->load('user');

		if(is_object($post)){
			$data = array(
				'code' => 200,
				'status' => 'success',
				'post' => $post
			);
		}else{

			$data = array(
				'code' => 404,
				'status' => 'error',
				'message' => 'El post no existe'
			);
		}

		return response()->json($data, $data['code']);
		
	}

	public function store(Request $request){

		// Get POST data
		$json = $request->input('json', null);
		$params = json_decode($json);
		$params_array = json_decode($json, true);

		if(!empty($params_array)){
			// Get user identificated

			$user = $this->getIdentity($request);

			// Validate post data

			$validate = \Validator::make($params_array, [

				'title' => 'required',
				'content' => 'required',
				'category_id' => 'required',
				'image' => 'required'
			]);

			if(!$validate->fails()){
				
				$post = new Post();
				$post->user_id = $user->sub;
				$post->category_id = $params->category_id;
				$post->title = $params->title;
				$post->content = $params->content;
				$post->image = $params->image;

				// Guardar el post
				$post->save();

				$data = array(
					'code' => 200,
					'status' => 'success',
					'message' => 'Post creado con exito',
					'post' => $post
				);

			}else{
				$data = array(
					'code' => 404,
					'status' => 'error',
					'message' => 'No se pudo crear el post',
					'error' => $validate->errors()
				);
			}
		}else{
			$data = array(
				'code' => 404,
				'status' => 'error',
				'message' => 'No se pudo crear el post'
			);
		}
		

		// Return response
		return response()->json($data, $data['code']);

	}
	

	public function update($id, Request $request){

		$json = $request->input('json', null);
		$params_array = json_decode($json, true);

		$data = array(
			'code' => 404,
			'status' => 'error',
			'message' => 'Datos enviados incorrectamente',
		);

		if(!empty($params_array)){
			$validate = \Validator::make($params_array, [
				'title' => 'required',
				'content' => 'required',
				'category_id' => 'required'
			]);
	
			if(!$validate->fails()){
				// Quitar campos para no actualizar
				unset($params_array['id']);
				unset($params_array['created_at']);
				unset($params_array['user_id']);
				unset($params_array['user']);

				$user = $this->getIdentity($request);
				
				$post = Post::where('id', $id)->where('user_id', $user->sub)->first();

				if(!empty($post) && is_object($post)){
					$data = array(
						'code' => 200,
						'status' => 'success',
						'message' => 'Post updated successfully',
						'post' => $post,
						'changes' => $params_array
					);
					$post->update($params_array);
				}

			}else{
				$data['errors'] = $validate->errors();
			}
		}

		return response()->json($data, $data['code']);

	}

	public function destroy($id, Request $request){

		$user = $this->getIdentity($request);

		// Conseguir el post
		$post = Post::where('id',$id)->where('user_id', $user->sub)->first();
		if(!empty($post)){
			// Borrarlo
			$post->delete();

			$data = array(
				'code' => 200,
				'status' => 'success',
				'message' => 'Post eliminado con exito',
				'post' => $post

			);
		}else{
			$data = array(
				'code' => 404,
				'status' => 'error',
				'message' => 'El post no existe'

			);
		}
		

		return response()->json($data, $data['code']);
	}

	public function upload(Request $request){
		// Get file from post
		$image = $request->file('file0');

		// Validate image
		$validate = \Validator::make($request->all(), [

			'file0' => 'required|image|mimes:jpg,png,jpeg,gif'
		]);

		// Save image on disk
		if($validate->fails()){
			$data = array(
				'code' => 400,
				'status' => 'error',
				'message' => 'Error al subir la imagen',
				'error' => $validate->errors()

			);
		}else{
			$image_name = time().$image->getClientOriginalName();

			\Storage::disk('images')->put($image_name, \File::get($image));
			$data = array(
				'code' => 200,
				'status' => 'success',
				'message' => 'Imagen subida con exito',
				'image' => $image_name

			);

		}
		// Return data
		return response()->json($data, $data['code']);
	}

	public function getImage($fileName){
		$isset = \Storage::disk('images')->exists($fileName);

		if($isset){
			$file = \Storage::disk('images')->get($fileName);

			return new Response($file, 200);
		}else{
			$data = array(
				'code' => 404,
				'status' => 'error',
				'message' => 'La imagen no existe'
			);
		}

		return response()->json($data, $data['code']);

		
	}

	public function getPostsByCategory($category_id){
		$posts = Post::where('category_id', $category_id)->get();

		return response()->json([
			'status' => 'success',
			'posts' => $posts
		], 200);
	}

	public function getPostsByUser($user_id){
		$posts = Post::where('user_id', $user_id)->get();

		return response()->json([
			'status' => 'success',
			'posts' => $posts
		], 200);
	}

	private function getIdentity($request){
		$JwtAuth = new JwtAuth();
		$token = $request->header('Authorization', null);
		$user = $JwtAuth->checkToken($token, true);

		return $user;
	}


	
}
