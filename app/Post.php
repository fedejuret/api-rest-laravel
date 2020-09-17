<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model{

	// Que tabla usaria de la base de datos
	protected $table = 'posts';

	protected $fillable = [
        'title', 'content', 'category_id', 'user_id', 'image'
    ];

	// Relacion de uno a muchos inversa (muchos a uno)

	// Esto sirve para que, cuando obtengamos un post obtengamos tambien el objeto de usuario
	// Tipo 'populate'
	public function user(){
		return $this->belongsTo('App\User', 'user_id');
	}
	
	public function category(){
		return $this->belongsTo('App\Category', 'category_id');
	}



}
