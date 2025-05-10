<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Menu\Fornecedor;

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

            if (Fornecedor::where('razao_Social', $validated['razao_Social'])->exists()) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Este fornecedor já está cadastrado no sistema.'
                ]);
            }

            Fornecedor::create($validated);

            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Fornecedor cadastrado com sucesso!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao cadastrar fornecedor: ' . $e->getMessage());
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao processar a solicitação: ' . $e->getMessage()
            ]);
        }
    }

    public function search(Request $request)
    {
        try {
            $query = Fornecedor::query();
            if ($request->filled('razao_Social')) {
                $query->where('razao_Social', 'like', '%' . $request->razao_Social . '%');
            }
            if ($request->filled('nome_Fantasia')) {
                $query->where('nome_Fantasia', 'like', '%' . $request->nome_Fantasia . '%');
            }
            if ($request->filled('apelido')) {
                $query->where('apelido', 'like', '%' . $request->apelido . '%');
            }
            if ($request->filled('grupo')) {
                $query->where('grupo', 'like', '%' . $request->grupo . '%');
            }
            if ($request->filled('sub_Grupo')) {
                $query->where('sub_Grupo', 'like', '%' . $request->sub_Grupo . '%');
            }
            $resultados = $query->get();
            if ($resultados->isEmpty()) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Nenhum fornecedor encontrado.'
                ]);
            }
            return response()->json([
                'status' => 'sucesso',
                'resultados' => $resultados
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar fornecedores: ' . $e->getMessage());
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
            $fornecedor = Fornecedor::find($id);
            if (!$fornecedor) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Fornecedor não encontrado.'
                ]);
            }
            return response()->json($fornecedor);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar fornecedor por ID: ' . $e->getMessage());
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
            $fornecedor = Fornecedor::find($validated['id_Fornecedor']);
            if (!$fornecedor) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Fornecedor não encontrado.'
                ]);
            }
            if ($fornecedor->razao_Social !== $validated['razao_Social']) {
                if (Fornecedor::where('razao_Social', $validated['razao_Social'])
                    ->where('id_Fornecedor', '!=', $validated['id_Fornecedor'])
                    ->exists()) {
                    return response()->json([
                        'status' => 'erro',
                        'mensagem' => 'Já existe outro fornecedor com esta Razão Social.'
                    ]);
                }
            }
            $fornecedor->update($validated);
            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Fornecedor atualizado com sucesso!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar fornecedor: ' . $e->getMessage());
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao processar a atualização: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $fornecedor = Fornecedor::find($id);
            if (!$fornecedor) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Fornecedor não encontrado.'
                ]);
            }
            $fornecedor->delete();
            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Fornecedor excluído com sucesso!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir fornecedor: ' . $e->getMessage());
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao excluir o fornecedor: ' . $e->getMessage()
            ]);
        }
    }
}
