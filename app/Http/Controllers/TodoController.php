<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Models\Todo;
use App\Models\User;
use Carbon\Carbon; 
use Session; 

class TodoController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$mensagens = Session::get('mensagens');

		$tarefas = Todo::select('todos.*','u.name as solicitante')
			->orderBy('finished','asc')
			->orderBy('updated_at','desc')
			->join('users as u','todos.user_id','u.id')
			->get();

		$users = User::all();
		return view('tarefas',compact('tarefas','mensagens','users'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create( Request $request)
	{
		$user_id = $request->input('user_id');
		$description = $request->input('description');

		$tarefa = new Todo ;
		$tarefa->finished = 0 ;
		$tarefa->user_id = $user_id ;
		$tarefa->description = $description ;
		$tarefa->created_at = Carbon::now() ;
		$tarefa->updated_at = Carbon::now() ;
		$tarefa->save();
		return redirect('tarefas');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request)
	{
		// Mensagens para debug
		$mensagens = array();
		
		$id = $request->input('tarefa_id');
		$tarefa = Todo::firstWhere('id',$id);
		$tarefa->updated_at = Carbon::now() ;
		
		if ( isset($_POST['finish']) ) {
			$tarefa->finished = 1 ;
			array_push($mensagens,"tarefa->finished = 1, " . $id);
			$tarefa->save();
	  	} 
		  elseif ( isset($_POST['reinsert']) ) {
			$tarefa->finished = 0 ;
			array_push($mensagens,"tarefa->finished = 0, " . $id );
			$tarefa->save();
	  	}
		  elseif ( isset($_POST['delete']) ) {
			$tarefa->finished = 0 ;
			array_push($mensagens,"apagar, " . $id );
			$tarefa->delete();
	  	}
		
		return redirect('/tarefas');
//		return redirect('/tarefas')->with(['mensagens' => $mensagens]);
	}
}
