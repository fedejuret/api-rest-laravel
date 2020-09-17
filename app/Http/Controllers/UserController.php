<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller
{



    public function register(Request $request){



		// Recoger los datos del usuario por post

		$json = $request->input("json", null);
		$params = json_decode($json);
		$params_array = json_decode($json, true);


		// Si el request trae parametros... continuamos
		if(!empty($params_array)){

			// Eliminamos los espacios de los parametros
			$params_array = array_map("trim", $params_array);

			// Validamos que cada input cumpla con su requerimiento
			$validate = \Validator::make($params_array, [
				"name" 		=> "required|alpha",
				"surname" 	=> "required|alpha",
				"email" 	=> "required|email|unique:users", // Comprobar disponibilidad del usuario "unique:{database_table}"
				"password" 	=> "required",
			]);

			// Check if validations has fails
			if($validate->fails()){
				$data = array(
		    		'status' => 'error',
		    		'code' => 404,
		    		'message' => 'El usuario no se ha creado correctamente',
		    		'errors' => $validate->errors()
		    	);
			}else{
				// Cifrar la contraseña
				$password_hashed = hash('sha256',$params->password);
				
				// Crear el usuario
				$user = new User();
                // Llenamos los campos del usuario
				$user->name = $params_array['name'];
				$user->surname = $params_array['surname'];
				$user->email = $params_array['email'];
				$user->password = $password_hashed;
				$user->role = 'ROLE_USER';
				
				// Guardar el usuario
				$user->save();

				$data = array(
		    		'status' => 'success',
		    		'code' => 200,
		    		'message' => 'El usuario se ha creado correctamente',
		    		'user' => $user
		    	);
			}
		}else{
			$data = array(
	    		'status' => 'error',
	    		'code' => 404,
	    		'message' => 'Los datos enviados no son correctos'
	    	);
		}

    	return response()->json($data, $data['code']);

    }

    public function login(Request $request){

    	$jwtAuth = new \JwtAuth();

    	$json = $request->input('json', null);
    	$params = json_decode($json);
    	$params_array = json_decode($json, true);

    	$validate = \Validator::make($params_array, [

    		'email' => 'required|email',
    		'password' => 'required'
    	]);

    	if($validate->fails()){
    		$signup = array(
    			'status' => 'error',
    			'code' => 404,
    			'message' => 'El usuario no pudo ser validado',
    			'errors' => $validate->errors()
    		);
    	}else{
    		$password_hashed = hash('sha256',$params->password);

    		$signup = $jwtAuth->signup($params->email, $password_hashed);

    		if(!empty($params->getToken)){
    			$signup = $jwtAuth->signup($params->email, $password_hashed, true);
    		}
    	}

    	return response()->json($signup, 200);
    }

    public function update(Request $request){

    	// Comprobar si el usuario esta identificado
    	$token = $request->header('Authorization');
    	$jwtAuth = new \JwtAuth();

    	$checkToken = $jwtAuth->checkToken($token);

    	$json = $request->input('json', null);

    	$params_array = json_decode($json, true);

    	if($checkToken && !empty($params_array)){
    		
    		
    		// Recoger los datos por post
    		

    		// Sacar usuario identificado
    		$user = $jwtAuth->checkToken($token, true);

    		// Validar los datos
    		$validate = \Validator::make($params_array, [
    			'name' => 'required|alpha',
    			'surname' => 'required|alpha',
    			'email' => 'required|email|unique:users,'.$user->sub
    		]);

    		// Quitar los campos que no quiero actualizar
			unset($params_array['id']);
    		unset($params_array['role']);
    		unset($params_array['password']);
    		unset($params_array['created_at']);
    		unset($params_array['remember_token']);


    		// Actualizar el usuario en la base de datos

    		$user_update = User::where('id', $user->sub)->update($params_array);

    		// Devolver array con resultado

    		$data = array(
    			'code' => 200,
    			'status' => 'success',
    			'user' => $user_update,
    			'changes' => $params_array
    		);

    	}else{
    		$data = array(
    			'code' => 400,
    			'status' => 'error',
    			'message' => 'Usuario no identificado'
    		);
    	}

    	return response()->json($data, $data['code']);
    }

    public function upload(Request $request){

    	$image = $request->file("file0");

    	$validate = \Validator::make($request->all(), [

    		'file0' => 'required|image|mimes:jpg,png,gif,jpeg'
    	]);

    	if(!$image || $validate->fails()){

			$data = array(
				'code' => 400,
				'status' => 'error',
				'message' => 'Error al subir la imagen',
				'error' => $validate->errors()
			);

    	}else{

			$imageName = time().$image->getClientOriginalName();
    		\Storage::disk('users')->put($imageName, \File::get($image));

    		$data = array(

    			'image' => $imageName,
    			'code' => 200,
    			'status' => 'success'
    		);
    	}

    	return response()->json($data, $data['code']);
    }

    public function getImage($fileName){

    	$isset = \Storage::disk('users')->exists($fileName);

    	if($isset){
    		$file = \Storage::disk('users')->get($fileName);

    		return new Response($file, 200);
    	}else{
    		$data = array(
    			'code' => 404,
    			'status' => 'error',
    			'message' => 'File not found'
    		);

    		return response()->json($data, $data['code']);
    	}

    }

    public function userDetail($id){
    	$user = User::find($id);

    	if(is_object($user)){
    		$data = array(
    			'code' => 200,
    			'status' => 'success',
    			'user' => $user
    		);
    	}else{
    		$data = array(
    			'code' => 404,
    			'status' => 'error',
    			'message' => 'El usuario no existe'
    		);
    	}

    	return response()->json($data, $data['code']);
    }
}
