<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerfilController extends Controller
{
    /**
     * Show the user profile page
     * 
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (!session('logged_in')) {
            return redirect()->route('auth.login');
        }
        
        $user = DB::table('usuarios')->where('id_Usuario', session('user_id'))->first();
        
        if (!$user) {
            return redirect()->route('auth.login');
        }
        
        return view('home.perfil', ['user' => $user]);
    }
    
    /**
     * Update the user's profile information
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        try {
            if (!session('logged_in')) {
                return response()->json([
                    'status' => 'error',
                    'mensagem' => 'Usuário não autenticado'
                ]);
            }
            
            $user_id = session('user_id');
            
            // Validate input
            $validated = $request->validate([
                'nome' => 'required|string|max:120',
                'sobrenome' => 'nullable|string|max:120',
                'login' => 'required|string|max:120|email|unique:usuarios,email,' . $user_id . ',id_Usuario',
                'senha' => 'nullable|string|min:4|max:60'
            ]);
            
            // Prepare update data de acordo com a estrutura da tabela de usuários
            $updateData = [
                'nome_Usuario' => $validated['nome'],
                'sobrenome' => $validated['sobrenome'],
                'email' => $validated['login'], // Usando o campo 'login' da requisição como email
                'updated_at' => now()
            ];
            
            // Only update password if provided
            if (!empty($validated['senha'])) {
                $updateData['senha'] = $validated['senha'];
            }
            
            // Update the user record
            DB::table('usuarios')
                ->where('id_Usuario', $user_id)
                ->update($updateData);
            
            // Update session data
            session(['user_name' => $validated['nome']]);
            
            return response()->json([
                'status' => 'success',
                'mensagem' => 'Perfil atualizado com sucesso!'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'mensagem' => $e->errors()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'mensagem' => 'Erro ao atualizar o perfil: ' . $e->getMessage()
            ]);
        }
    }
}