<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Models\Todo;
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

		$tarefas = Todo::orderBy('finished','asc')->get();
		return view('tarefas',compact('tarefas','mensagens'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create( Request $request)
	{
		$description = $request->input('description');

		$tarefa = new Todo ;
		$tarefa->finished = 0 ;
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
