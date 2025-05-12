<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\Menu\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UsuarioController extends Controller
{
    public function index()
    {
        return view('menu.usuarios.usuarios');
    }

    public function showBuscar()
    {
        return view('menu.usuarios.usuarios-buscar');
    }

    public function showEditar(Request $request)
    {
        $id = $request->query('id');
        if (!$id) {
            return redirect()->route('menu.usuarios.usuarios-buscar')
                ->with('error', 'ID do usuário não informado');
        }
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return redirect()->route('menu.usuarios.usuarios-buscar')
                ->with('error', 'Usuário não encontrado');
        }
        return view('menu.usuarios.usuarios-editar', ['usuario' => $usuario]);
    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'searchTerm' => 'required|string|max:50',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Termo de busca inválido'
            ], 422);
        }
        $searchTerm = $request->searchTerm;
        $usuarios = Usuario::where('nome_Usuario', 'like', "%{$searchTerm}%")
            ->orWhere('email', 'like', "%{$searchTerm}%")
            ->get();
        return response()->json([
            'status' => 'sucesso',
            'usuarios' => $usuarios
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|max:50',
            'sobrenome' => 'nullable|max:50',
            'nivel' => 'required|in:admin,padrao',
            'funcao' => 'required|max:50',
            'login' => 'required|max:20|unique:usuarios,nome_Usuario',
            'senha' => 'required|digits:4',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => $validator->errors()->first()
            ], 422);
        }
        $usuario = new Usuario();
        $usuario->nome_Usuario = $request->nome;
        $usuario->sobrenome = $request->sobrenome;
        $usuario->funcao = $request->funcao;
        $usuario->email = $request->login;
        $usuario->tipo_Usuario = $request->nivel;
        $usuario->nivel_Usuario = $request->nivel;
        $usuario->senha = $request->senha;
        $usuario->save();
        return response()->json([
            'status' => 'sucesso',
            'mensagem' => 'Usuário cadastrado com sucesso!'
        ]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:usuarios,id_Usuario',
            'nivel' => 'required|in:admin,padrao',
            'nome' => 'required|max:50',
            'email' => 'required|email|max:100',
            'senha' => 'nullable|digits:4',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => $validator->errors()->first()
            ], 422);
        }
        $usuario = Usuario::find($request->id);
        if ($usuario->tipo_Usuario === 'admin' && $request->nivel !== 'admin') {
            $adminCount = Usuario::where('tipo_Usuario', 'admin')->count();
            if ($adminCount <= 1) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Não é possível rebaixar o último administrador do sistema'
                ], 422);
            }
        }
        $emailExists = Usuario::where('email', $request->email)
            ->where('id_Usuario', '!=', $request->id)
            ->exists();
        if ($emailExists) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Este email já está em uso por outro usuário'
            ], 422);
        }
        $usuario->nome_Usuario = $request->nome;
        $usuario->email = $request->email;
        $usuario->tipo_Usuario = $request->nivel;
        $usuario->nivel_Usuario = $request->nivel;
        if ($request->filled('senha')) {
            $usuario->senha = $request->senha;
        }
        $usuario->save();
        return response()->json([
            'status' => 'sucesso',
            'mensagem' => 'Usuário atualizado com sucesso!'
        ]);
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:usuarios,id_Usuario',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => $validator->errors()->first()
            ], 422);
        }
        $usuario = Usuario::find($request->id);
        if ($usuario->tipo_Usuario === 'admin') {
            $adminCount = Usuario::where('tipo_Usuario', 'admin')->count();
            if ($adminCount <= 1) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Não é possível excluir o último administrador do sistema'
                ], 422);
            }
        }
        $usuario->delete();
        return response()->json([
            'status' => 'sucesso',
            'mensagem' => 'Usuário excluído com sucesso!'
        ]);
    }
}