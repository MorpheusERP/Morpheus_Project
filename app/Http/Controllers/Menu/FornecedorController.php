<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FornecedorController extends Controller
{
    public function index()
    {
        return view('menu.fornecedor.fornecedor');
    }

    public function showBuscar()
    {
        return view('menu.fornecedor.fornecedor-buscar');
    }

    public function showEditar()
    {
        return view('menu.fornecedor.fornecedor-editar');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'razao_Social' => 'required|string|max:150',
                'nome_Fantasia' => 'nullable|string|max:50',
                'apelido' => 'nullable|string|max:50',
                'grupo' => 'required|string|max:50',
                'sub_Grupo' => 'nullable|string|max:50',
                'observacao' => 'nullable|string|max:150',
            ]);

            $existingFornecedor = DB::table('fornecedores')
                ->where('razao_Social', $validated['razao_Social'])
                ->first();

            if ($existingFornecedor) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Este fornecedor já está cadastrado no sistema.'
                ]);
            }

            DB::table('fornecedores')->insert([
                'razao_Social' => $validated['razao_Social'],
                'nome_Fantasia' => $validated['nome_Fantasia'] ?? null,
                'apelido' => $validated['apelido'] ?? null,
                'grupo' => $validated['grupo'],
                'sub_Grupo' => $validated['sub_Grupo'] ?? null,
                'observacao' => $validated['observacao'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Fornecedor cadastrado com sucesso!'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao cadastrar fornecedor: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao processar a solicitação: ' . $e->getMessage()
            ]);
        }
    }

    public function search(Request $request)
    {
        try {
            $termo = $request->input('termo');

            $query = DB::table('fornecedores')
                ->select('id_Fornecedor', 'razao_Social', 'nome_Fantasia', 'grupo');

            if (!empty($termo)) {
                $query->where(function ($q) use ($termo) {
                    $q->where('razao_Social', 'LIKE', '%' . $termo . '%')
                      ->orWhere('nome_Fantasia', 'LIKE', '%' . $termo . '%')
                      ->orWhere('grupo', 'LIKE', '%' . $termo . '%');
                });
            }

            $fornecedores = $query->get();

            if ($fornecedores->isEmpty()) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Nenhum fornecedor encontrado.'
                ]);
            }

            return response()->json([
                'status' => 'sucesso',
                'fornecedores' => $fornecedores
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar fornecedores: ' . $e->getMessage());

            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Erro ao buscar fornecedores: ' . $e->getMessage()
            ]);
        }
    }

    public function find(Request $request)
    {
        try {
            $id = $request->id_Fornecedor;
            
            $fornecedor = DB::table('fornecedores')
                ->where('id_Fornecedor', $id)
                ->first();

            if (!$fornecedor) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Fornecedor não encontrado.'
                ]);
            }

            return response()->json($fornecedor);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar fornecedor por ID: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao buscar o fornecedor: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_Fornecedor' => 'required|integer',
                'razao_Social' => 'required|string|max:150',
                'nome_Fantasia' => 'nullable|string|max:50',
                'apelido' => 'nullable|string|max:50',
                'grupo' => 'required|string|max:50',
                'sub_Grupo' => 'nullable|string|max:50',
                'observacao' => 'nullable|string|max:150',
            ]);

            $fornecedor = DB::table('fornecedores')
                ->where('id_Fornecedor', $validated['id_Fornecedor'])
                ->first();

            if (!$fornecedor) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Fornecedor não encontrado.'
                ]);
            }

            if ($fornecedor->razao_Social !== $validated['razao_Social']) {
                $duplicate = DB::table('fornecedores')
                    ->where('razao_Social', $validated['razao_Social'])
                    ->where('id_Fornecedor', '!=', $validated['id_Fornecedor'])
                    ->first();

                if ($duplicate) {
                    return response()->json([
                        'status' => 'erro',
                        'mensagem' => 'Já existe outro fornecedor com esta Razão Social.'
                    ]);
                }
            }

            DB::table('fornecedores')
                ->where('id_Fornecedor', $validated['id_Fornecedor'])
                ->update([
                    'razao_Social' => $validated['razao_Social'],
                    'nome_Fantasia' => $validated['nome_Fantasia'] ?? null,
                    'apelido' => $validated['apelido'] ?? null,
                    'grupo' => $validated['grupo'],
                    'sub_Grupo' => $validated['sub_Grupo'] ?? null,
                    'observacao' => $validated['observacao'] ?? null,
                    'updated_at' => now()
                ]);

            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Fornecedor atualizado com sucesso!'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao atualizar fornecedor: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao processar a atualização: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $fornecedor = DB::table('fornecedores')
                ->where('id_Fornecedor', $id)
                ->first();

            if (!$fornecedor) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Fornecedor não encontrado.'
                ]);
            }

            DB::table('fornecedores')
                ->where('id_Fornecedor', $id)
                ->delete();

            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Fornecedor excluído com sucesso!'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao excluir fornecedor: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao excluir o fornecedor: ' . $e->getMessage()
            ]);
        }
    }
}
