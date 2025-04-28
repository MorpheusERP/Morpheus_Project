<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UsuarioController extends Controller
{
    public function index()
    {
        // Verifica se o usuário está logado
        if (!session('logged_in')) {
            return redirect()->route('login');
        }

        // Verifica se o usuário é um administrador
        if (session('user_type') !== 'admin') {
            return redirect()->route('home')->with('error', 'Acesso negado. Você não possui permissão para acessar essa página.');
        }

        return view('menu.usuarios.usuarios');
    }

    public function buscarIndex()
    {
        // Verifica se o usuário está logado
        if (!session('logged_in')) {
            return redirect()->route('login');
        }

        // Verifica se o usuário é um administrador
        if (session('user_type') !== 'admin') {
            return redirect()->route('home')->with('error', 'Acesso negado. Você não possui permissão para acessar essa página.');
        }

        return view('menu.usuarios.usuarios-buscar');
    }

    public function editarIndex(Request $request)
    {
        // Verifica se o usuário está logado
        if (!session('logged_in')) {
            return redirect()->route('login');
        }

        // Verifica se o usuário é um administrador
        if (session('user_type') !== 'admin') {
            return redirect()->route('home')->with('error', 'Acesso negado. Você não possui permissão para acessar essa página.');
        }

        $id = $request->input('id');
        
        if (!$id) {
            return redirect()->route('menu.usuarios.usuarios-buscar')
                ->with('error', 'ID do usuário não fornecido.');
        }

        $usuario = DB::table('usuarios')->where('id_Usuario', $id)->first();
        
        if (!$usuario) {
            return redirect()->route('menu.usuarios.usuarios-buscar')
                ->with('error', 'Usuário não encontrado.');
        }

        return view('menu.usuarios.usuarios-editar', compact('usuario'));
    }

    public function store(Request $request)
    {
        // Validar entrada
        $validator = Validator::make($request->all(), [
            'nivel' => 'required|in:padrao,admin',
            'nome' => 'required|string|max:120',
            'sobrenome' => 'nullable|string|max:120',
            'funcao' => 'required|string|max:120',
            'login' => 'required|string|unique:usuarios,nome_Usuario|max:120',
            'senha' => 'required|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => $validator->errors()->first()
            ]);
        }

        try {
            // Insere o novo usuário no banco de dados
            DB::table('usuarios')->insert([
                'nivel_Usuario' => $request->nivel,
                'nome_Usuario' => $request->login,
                'sobrenome' => $request->sobrenome,
                'funcao' => $request->funcao,
                'email' => $request->login,  // Usando o login como email por enquanto
                'tipo_Usuario' => $request->nivel,
                'senha' => Hash::make($request->senha),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Usuário cadastrado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Erro ao cadastrar usuário: ' . $e->getMessage()
            ]);
        }
    }

    public function search(Request $request)
    {
        $searchTerm = $request->input('searchTerm');

        if (empty($searchTerm)) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Termo de busca não fornecido',
                'usuarios' => []
            ]);
        }

        try {
            $usuarios = DB::table('usuarios')
                ->where('nome_Usuario', 'like', '%' . $searchTerm . '%')
                ->orWhere('email', 'like', '%' . $searchTerm . '%')
                ->select('id_Usuario as id', 'nome_Usuario', 'email', 'tipo_Usuario')
                ->get();

            return response()->json([
                'status' => 'sucesso',
                'usuarios' => $usuarios
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Erro ao buscar usuários: ' . $e->getMessage(),
                'usuarios' => []
            ]);
        }
    }

    public function update(Request $request)
    {
        // Validar entrada
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:usuarios,id_Usuario',
            'nivel' => 'required|in:padrao,admin',
            'nome' => 'required|string|max:120',
            'email' => 'required|string|max:120',
            'senha' => 'nullable|digits:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => $validator->errors()->first()
            ]);
        }

        try {
            $updateData = [
                'nome_Usuario' => $request->nome,
                'email' => $request->email,
                'tipo_Usuario' => $request->nivel,
                'nivel_Usuario' => $request->nivel,
                'updated_at' => now()
            ];

            // Adicionar senha apenas se foi enviada
            if ($request->filled('senha')) {
                $updateData['senha'] = Hash::make($request->senha);
            }

            // Atualiza o usuário
            DB::table('usuarios')
                ->where('id_Usuario', $request->id)
                ->update($updateData);

            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Usuário atualizado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Erro ao atualizar usuário: ' . $e->getMessage()
            ]);
        }
    }
}