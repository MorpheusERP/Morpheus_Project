<?php

namespace App\Http\Controllers\Relatorio;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class RelatorioUsuarioController extends Controller
{
    public function index()
    {
        return view('menu.relatorio.usuario-relatorio');
    }

    public function search(Request $request)
    {
        try {
            $nome = $request->input('nome');
            $tipo = $request->input('tipo');
            
            Log::info('Buscando usuários para relatório:', [
                'nome' => $nome,
                'tipo' => $tipo
            ]);
            
            $query = User::query();

            if (!empty($nome)) {
                $query->where('nome_Usuario', 'like', '%' . $nome . '%');
            }
            
            // Apenas aplica o filtro de tipo_Usuario se não for vazio (ou seja, se não for "Todos")
            if (!empty($tipo)) {
                $query->where('tipo_Usuario', $tipo);
                Log::info('Aplicando filtro de tipo_Usuario:', ['tipo' => $tipo]);
            }

            $usuarios = $query->get([
                'id_Usuario', 
                'nome_Usuario', 
                'email', 
                'tipo_Usuario',
                'nivel_Usuario',
                'funcao'
            ]);
            
            Log::info('Usuários encontrados para relatório:', [
                'count' => $usuarios->count(),
                'primeiro_usuario' => $usuarios->first()
            ]);
            
            return response()->json([
                'status' => 'success',
                'usuarios' => $usuarios
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar usuários para relatório', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Ocorreu um erro ao buscar usuários: ' . $e->getMessage()
            ], 500);
        }
    }
}