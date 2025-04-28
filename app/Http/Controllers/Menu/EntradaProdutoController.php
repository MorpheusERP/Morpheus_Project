<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntradaProdutoController extends Controller
{
    public function index()
    {
        return view('menu.entrada-produtos.entrada-produtos');
    }
    
    public function showBuscar()
    {
        return view('menu.entrada-produtos.entrada-produtos-buscar');
    }
    
    public function showEditar()
    {
        return view('menu.entrada-produtos.entrada-produtos-editar');
    }

    public function store(Request $request)
    {
        $userId = session('user_id');
        $userName = session('user_name');
        if (!$userId || !$userName) {
            \Log::error('Usuário não autenticado via sessão no método store de EntradaProdutoController', [
                'session' => session()->all()
            ]);
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Usuário não autenticado. Por favor, faça login novamente.'
            ]);
        }

        try {
            $validated = $request->validate([
                'cod_Produto' => 'required|integer',
                'id_Fornecedor' => 'required|integer',
                'qtd_Entrada' => 'required|numeric|min:0.01',
                'preco_Custo' => 'required|numeric|min:0.01',
                'preco_Venda' => 'nullable|numeric|min:0',
                'data_Entrada' => 'required|date',
                'observacao' => 'nullable|string|max:150',
            ]);

            $produto = DB::table('produtos')->where('cod_Produto', $validated['cod_Produto'])->first();
            if (!$produto) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Produto não encontrado.'
                ]);
            }

            $fornecedor = DB::table('fornecedores')->where('id_Fornecedor', $validated['id_Fornecedor'])->first();
            if (!$fornecedor) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Fornecedor não encontrado.'
                ]);
            }

            $estoque = DB::table('estoques')->where('cod_Produto', $validated['cod_Produto'])->first();
            if (!$estoque) {
                $idEstoque = DB::table('estoques')->insertGetId([
                    'cod_Produto' => $validated['cod_Produto'],
                    'qtd_Estoque' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                $estoque = DB::table('estoques')->where('id_Estoque', $idEstoque)->first();
            }

            $valorTotal = $validated['preco_Custo'] * $validated['qtd_Entrada'];

            DB::beginTransaction();
            try {
                $idEntrada = DB::table('entrada_produtos')->insertGetId([
                    'id_Usuario' => $userId,
                    'nome_Usuario' => $userName,
                    'id_Fornecedor' => $fornecedor->id_Fornecedor,
                    'razao_Social' => $fornecedor->razao_Social,
                    'cod_Produto' => $produto->cod_Produto,
                    'nome_Produto' => $produto->nome_Produto,
                    'id_Estoque' => $estoque->id_Estoque,
                    'qtd_Entrada' => $validated['qtd_Entrada'],
                    'preco_Custo' => $validated['preco_Custo'],
                    'preco_Venda' => $validated['preco_Venda'] ?? null,
                    'valor_Total' => $valorTotal,
                    'data_Entrada' => $validated['data_Entrada'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::table('estoques')
                    ->where('id_Estoque', $estoque->id_Estoque)
                    ->update([
                        'qtd_Estoque' => DB::raw('qtd_Estoque + ' . $validated['qtd_Entrada']),
                        'updated_at' => now()
                    ]);

                DB::table('produtos')
                    ->where('cod_Produto', $produto->cod_Produto)
                    ->update([
                        'preco_Custo' => $validated['preco_Custo'],
                        'preco_Venda' => $validated['preco_Venda'] ?? $produto->preco_Venda,
                        'preco_Custo' => $validated['preco_Custo'],
                        'preco_Venda' => $validated['preco_Venda'] ?? $produto->preco_Venda,
                        'updated_at' => now()
                    ]);

                DB::commit();

                return response()->json([
                    'status' => 'sucesso',
                    'mensagem' => 'Entrada de produto registrada com sucesso.',
                    'id_Entrada' => $idEntrada
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao registrar entrada de produto: ' . $e->getMessage());
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Erro ao registrar entrada: ' . $e->getMessage()
            ]);
        }
    }

    public function search(Request $request)
    {
        try {
            $termo = $request->input('termo');
            $query = DB::table('entrada_produtos')
                ->leftJoin('fornecedores', 'entrada_produtos.id_Fornecedor', '=', 'fornecedores.id_Fornecedor')
                ->leftJoin('produtos', 'entrada_produtos.cod_Produto', '=', 'produtos.cod_Produto')
                ->select(
                    'entrada_produtos.*',
                    'fornecedores.nome_Fantasia',
                    'produtos.imagem'
                );
            if (!empty($termo)) {
                $query->where(function ($q) use ($termo) {
                    $q->where('entrada_produtos.cod_Produto', 'LIKE', '%' . $termo . '%')
                      ->orWhere('entrada_produtos.nome_Produto', 'LIKE', '%' . $termo . '%')
                      ->orWhere('entrada_produtos.razao_Social', 'LIKE', '%' . $termo . '%')
                      ->orWhere('fornecedores.nome_Fantasia', 'LIKE', '%' . $termo . '%');
                });
            }
            $entradas = $query->orderBy('entrada_produtos.data_Entrada', 'desc')->get();
            if ($entradas->isEmpty()) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Nenhuma entrada de produto encontrada.'
                ]);
            }
            foreach ($entradas as $entrada) {
                if ($entrada->imagem) {
                    $entrada->imagem = base64_encode($entrada->imagem);
                }
            }
            return response()->json([
                'status' => 'sucesso',
                'entradas' => $entradas
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar entradas de produtos: ' . $e->getMessage());
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao processar a busca: ' . $e->getMessage()
            ]);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $request->validate([
                'id_Entrada' => 'required|integer',
            ]);
            
            $id_Entrada = $request->input('id_Entrada');

            // Verificar se a entrada existe
            $entrada = DB::table('entrada_produtos')->where('id_Entrada', $id_Entrada)->first();
            
            if (!$entrada) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Registro de entrada não encontrado.'
                ]);
            }

            // Iniciar transação
            DB::beginTransaction();

            try {
                // Reduzir quantidade do estoque
                DB::table('estoques')
                    ->where('id_Estoque', $entrada->id_Estoque)
                    ->update([
                        'qtd_Estoque' => DB::raw('qtd_Estoque - ' . $entrada->qtd_Entrada),
                        'updated_at' => now()
                    ]);

                // Excluir o registro de entrada
                DB::table('entrada_produtos')->where('id_Entrada', $id_Entrada)->delete();

                DB::commit();

                return response()->json([
                    'status' => 'sucesso',
                    'mensagem' => 'Entrada de produto excluída com sucesso.'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir entrada de produto: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao excluir a entrada de produto: ' . $e->getMessage()
            ]);
        }
    }

    public function find(Request $request)
    {
        try {
            // Check if we're looking for a specific entrada or a batch of entradas
            if ($request->has('id_Entrada')) {
                $id = $request->input('id_Entrada');
                
                if (!$id) {
                    return response()->json([
                        'status' => 'erro',
                        'mensagem' => 'ID da entrada não fornecido.'
                    ]);
                }
                
                $entrada = DB::table('entrada_produtos')
                    ->leftJoin('fornecedores', 'entrada_produtos.id_Fornecedor', '=', 'fornecedores.id_Fornecedor')
                    ->leftJoin('produtos', 'entrada_produtos.cod_Produto', '=', 'produtos.cod_Produto')
                    ->where('entrada_produtos.id_Entrada', $id)
                    ->select(
                        'entrada_produtos.*',
                        'fornecedores.nome_Fantasia',
                        'produtos.imagem'
                    )
                    ->first();

                if (!$entrada) {
                    return response()->json([
                        'status' => 'erro',
                        'mensagem' => 'Entrada de produto não encontrada.'
                    ]);
                }

                // Processar imagem em base64
                if ($entrada->imagem) {
                    $entrada->imagem = base64_encode($entrada->imagem);
                }

                return response()->json([
                    'status' => 'sucesso',
                    'entrada' => $entrada
                ]);
            } 
            // If we're looking for a batch (id_Lote)
            elseif ($request->has('id_Lote')) {
                $id_Lote = $request->input('id_Lote');
                
                $entradas = DB::table('entrada_produtos')
                    ->leftJoin('fornecedores', 'entrada_produtos.id_Fornecedor', '=', 'fornecedores.id_Fornecedor')
                    ->leftJoin('produtos', 'entrada_produtos.cod_Produto', '=', 'produtos.cod_Produto')
                    ->where('entrada_produtos.id_Lote', $id_Lote)
                    ->select(
                        'entrada_produtos.*',
                        'fornecedores.nome_Fantasia',
                        'produtos.imagem'
                    )
                    ->get();

                if ($entradas->isEmpty()) {
                    return response()->json([
                        'status' => 'erro',
                        'mensagem' => 'Nenhuma entrada de produto encontrada para este lote.'
                    ]);
                }

                // Processar imagens em base64
                foreach ($entradas as $entrada) {
                    if ($entrada->imagem) {
                        $entrada->imagem = base64_encode($entrada->imagem);
                    }
                }

                return response()->json([
                    'status' => 'sucesso',
                    'produtos' => $entradas
                ]);
            }
            else {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Parâmetro de busca não fornecido.'
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar detalhes da entrada de produto: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao buscar os detalhes da entrada de produto: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_Entrada' => 'required|integer',
                'id_Fornecedor' => 'required|integer',
                'qtd_Entrada' => 'required|numeric|min:0.01',
                'preco_Custo' => 'required|numeric|min:0.01',
                'preco_Venda' => 'nullable|numeric|min:0',
                'data_Entrada' => 'required|date',
            ]);

            // Verificar se a entrada existe
            $entrada = DB::table('entrada_produtos')
                ->where('id_Entrada', $validated['id_Entrada'])
                ->first();

            if (!$entrada) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Registro de entrada não encontrado.'
                ]);
            }

            // Verificar se o fornecedor existe
            $fornecedor = DB::table('fornecedores')
                ->where('id_Fornecedor', $validated['id_Fornecedor'])
                ->first();

            if (!$fornecedor) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Fornecedor não encontrado.'
                ]);
            }

            // Calcular a diferença de quantidade
            $diferenca = $validated['qtd_Entrada'] - $entrada->qtd_Entrada;
            
            // Se a diferença for negativa (reduzindo quantidade), verificar se há estoque suficiente
            if ($diferenca < 0) {
                // Buscar estoque atual
                $estoque = DB::table('estoques')
                    ->where('id_Estoque', $entrada->id_Estoque)
                    ->first();
                
                if (!$estoque) {
                    return response()->json([
                        'status' => 'erro',
                        'mensagem' => 'Registro de estoque não encontrado.'
                    ]);
                }
                
                // Verificar se a redução vai deixar o estoque negativo
                if ($estoque->qtd_Estoque < abs($diferenca)) {
                    return response()->json([
                        'status' => 'erro',
                        'mensagem' => 'Não é possível reduzir a quantidade. Estoque atual: ' . $estoque->qtd_Estoque
                    ]);
                }
            }
            
            // Calcular novo valor total
            $valorTotal = $validated['preco_Custo'] * $validated['qtd_Entrada'];

            // Atualizar registro
            DB::beginTransaction();
            try {
                // Atualizar registro de entrada
                DB::table('entrada_produtos')
                    ->where('id_Entrada', $validated['id_Entrada'])
                    ->update([
                        'id_Fornecedor' => $fornecedor->id_Fornecedor,
                        'razao_Social' => $fornecedor->razao_Social,
                        'qtd_Entrada' => $validated['qtd_Entrada'],
                        'preco_Custo' => $validated['preco_Custo'],
                        'preco_Venda' => $validated['preco_Venda'],
                        'valor_Total' => $valorTotal,
                        'data_Entrada' => $validated['data_Entrada'],
                        'updated_at' => now()
                    ]);

                // Atualizar estoque de acordo com a diferença
                if ($diferenca != 0) {
                    DB::table('estoques')
                        ->where('id_Estoque', $entrada->id_Estoque)
                        ->update([
                            'qtd_Estoque' => DB::raw('qtd_Estoque + ' . $diferenca),
                            'updated_at' => now()
                        ]);
                }

                DB::commit();

                return response()->json([
                    'status' => 'sucesso',
                    'mensagem' => 'Entrada de produto atualizada com sucesso.'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar entrada de produto: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao atualizar a entrada de produto: ' . $e->getMessage()
            ]);
        }
    }

    // Adicionando método para buscar fornecedores cadastrados
    public function getFornecedores()
    {
        try {
            $fornecedores = DB::table('fornecedores')->select('id_Fornecedor', 'razao_Social')->get();

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
}