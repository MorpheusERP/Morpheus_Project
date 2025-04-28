<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use App\Models\Menu\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
        try {
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
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar usuários', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao buscar usuários: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('Requisição recebida', ['data' => $request->all()]);
            
            $validator = Validator::make($request->all(), [
                'nome' => 'required|max:50',
                'sobrenome' => 'nullable|max:50',
                'nivel' => 'required|in:admin,padrao',
                'funcao' => 'required|max:50',
                'login' => 'required|max:20|unique:usuarios,nome_Usuario',
                'senha' => 'required|digits:4',
            ], [
                'nome.required' => 'O nome é obrigatório',
                'nome.max' => 'O nome não pode ter mais de 50 caracteres',
                'sobrenome.max' => 'O sobrenome não pode ter mais de 50 caracteres',
                'nivel.required' => 'O nível de usuário é obrigatório',
                'nivel.in' => 'O nível de usuário deve ser admin ou padrão',
                'funcao.required' => 'A função é obrigatória',
                'funcao.max' => 'A função não pode ter mais de 50 caracteres',
                'login.required' => 'O login é obrigatório',
                'login.max' => 'O login não pode ter mais de 20 caracteres',
                'login.unique' => 'Este login já está em uso',
                'senha.required' => 'A senha é obrigatória',
                'senha.digits' => 'A senha deve ter exatamente 4 dígitos',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => $validator->errors()->first()
                ], 422);
            }

            $nomeCompleto = $request->nome;
            if ($request->sobrenome) {
                $nomeCompleto .= ' ' . $request->sobrenome;
            }

            $email = strtolower($request->login);

            $usuario = new Usuario();
            $usuario->nome_Usuario = $nomeCompleto;
            $usuario->email = $request->login;
            $usuario->tipo_Usuario = $request->nivel;
            $usuario->nivel_Usuario = $request->nivel;
            $usuario->funcao = $request->funcao;
            $usuario->sobrenome = $request->sobrenome;
            $usuario->senha = $request->senha;
            $usuario->save();

            Log::info('Usuário criado com sucesso', ['usuario_id' => $usuario->id_Usuario]);

            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Usuário cadastrado com sucesso!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao cadastrar usuário', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao cadastrar o usuário: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:usuarios,id_Usuario',
                'nivel' => 'required|in:admin,padrao',
                'nome' => 'required|max:50',
                'email' => 'required|email|max:100',
                'senha' => 'nullable|digits:4',
            ], [
                'id.required' => 'ID do usuário não informado',
                'id.exists' => 'Usuário não encontrado',
                'nivel.required' => 'O nível de usuário é obrigatório',
                'nivel.in' => 'O nível de usuário deve ser admin ou padrão',
                'nome.required' => 'O nome é obrigatório',
                'nome.max' => 'O nome não pode ter mais de 50 caracteres',
                'email.required' => 'O email é obrigatório',
                'email.email' => 'Email inválido',
                'email.max' => 'O email não pode ter mais de 100 caracteres',
                'senha.digits' => 'A senha deve ter exatamente 4 dígitos',
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
            
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar usuário', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao atualizar o usuário: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
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
            
        } catch (\Exception $e) {
            Log::error('Erro ao excluir usuário', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao excluir o usuário: ' . $e->getMessage()
            ], 500);
        }
    }
}