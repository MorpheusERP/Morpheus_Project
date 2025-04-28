<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PasswordResetController extends Controller
{
    /**
     * Show the reset form.
     *
     * @return \Illuminate\View\View
     */
    public function showResetForm()
    {
        return view('auth.redefinir');
    }

    /**
     * Process the reset request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'senha' => 'required|string',
        ]);

        // Verificar se é um administrador
        $user = DB::table('usuarios')
            ->where('email', $credentials['email'])
            ->where('tipo_Usuario', 'admin')
            ->first();

        if (!$user) {
            return back()->with('error', 'Email de administrador não encontrado');
        }

        // Verificar senha
        $passwordMatches = false;
        
        // Verificar se a senha é um hash bcrypt
        if (strlen($user->senha) > 20) {
            $passwordMatches = Hash::check($credentials['senha'], $user->senha);
        } else {
            $passwordMatches = $credentials['senha'] === $user->senha;
        }

        if (!$passwordMatches) {
            return back()->with('error', 'Senha de administrador incorreta');
        }

        // Armazenar informações na sessão para o próximo passo
        session([
            'admin_verified' => true,
            'admin_id' => $user->id_Usuario
        ]);

        return redirect()->route('auth.novasenha');
    }

    /**
     * Show new password form.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showNovaSenhaForm()
    {
        if (!session('admin_verified')) {
            return redirect()->route('auth.redefinir')
                ->with('error', 'Por favor, verifique suas credenciais primeiro');
        }

        return view('auth.novasenha');
    }

    /**
     * Save the new password.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveNewPassword(Request $request)
    {
        if (!session('admin_verified')) {
            return redirect()->route('auth.redefinir')
                ->with('error', 'Por favor, verifique suas credenciais primeiro');
        }

        $request->validate([
            'login' => 'required|string',
            'senha' => 'required|string|min:4',
        ]);

        // Buscar o usuário pelo login
        $user = DB::table('usuarios')
            ->where('nome_Usuario', $request->login)
            ->first();

        if (!$user) {
            return back()->with('error', 'Usuário não encontrado');
        }

        try {
            // Atualizar a senha do usuário
            DB::table('usuarios')
                ->where('id_Usuario', $user->id_Usuario)
                ->update([
                    'senha' => Hash::make($request->senha),
                    'updated_at' => now()
                ]);

            // Limpar a sessão de verificação de admin
            session()->forget(['admin_verified', 'admin_id']);

            return redirect()->route('auth.login')
                ->with('status', 'Senha atualizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar senha: ' . $e->getMessage());
            return back()->with('error', 'Ocorreu um erro ao atualizar a senha');
        }
    }
}