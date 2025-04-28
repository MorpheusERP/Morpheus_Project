<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaidaProdutoController extends Controller
{
    public function index()
    {
        return view('menu.saida-produtos.saida-produtos');
    }
    
    public function showBuscar()
    {
        return view('menu.saida-produtos.saida-produtos-buscar');
    }
    
    public function showEditar()
    {
        return view('menu.saida-produtos.saida-produtos-editar');
    }

    // ...existing code...

    public function store(Request $request)
    {
        // Buscar usuário da sessão (não do Auth)
        $userId = session('user_id');
        $userName = session('user_name');
        if (!$userId || !$userName) {
            \Log::error('Usuário não autenticado via sessão no método store de SaidaProdutoController', [
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
                'id_Local' => 'required|integer',
                'qtd_Saida' => 'required|numeric|min:0.01',
                'data_Saida' => 'required|date',
                'observacao' => 'nullable|string|max:120',
            ]);

            // Verificar se o produto existe
            $produto = DB::table('produtos')->where('cod_Produto', $validated['cod_Produto'])->first();
            if (!$produto) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Produto não encontrado.'
                ]);
            }

            // Verificar se o local de destino existe
            $localDestino = DB::table('local_destinos')->where('id_Local', $validated['id_Local'])->first();
            if (!$localDestino) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Local de destino não encontrado.'
                ]);
            }

            // Verificar estoque
            $estoque = DB::table('estoques')->where('cod_Produto', $validated['cod_Produto'])->first();
            if (!$estoque) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Registro de estoque não encontrado para este produto.'
                ]);
            }
            
            if ($estoque->qtd_Estoque < $validated['qtd_Saida']) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Estoque insuficiente para realizar a saída. Disponível: ' . $estoque->qtd_Estoque
                ]);
            }

            // Calcular valor total
            $valorTotal = $produto->preco_Custo * $validated['qtd_Saida'];

            // Registrar saída
            DB::beginTransaction();
            try {
                $idSaida = DB::table('saida_produtos')->insertGetId([
                    'imagem' => $produto->imagem,
                    'id_Usuario' => $userId,
                    'nome_Usuario' => $userName,
                    'cod_Produto' => $produto->cod_Produto,
                    'nome_Produto' => $produto->nome_Produto,
                    'preco_Custo' => $produto->preco_Custo,
                    'id_Local' => $localDestino->id_Local,
                    'nome_Local' => $localDestino->nome_Local,
                    'id_Estoque' => $estoque->id_Estoque,
                    'qtd_Saida' => $validated['qtd_Saida'],
                    'valor_Total' => $valorTotal,
                    'observacao' => $validated['observacao'],
                    'data_Saida' => $validated['data_Saida'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Atualizar estoque
                DB::table('estoques')
                    ->where('id_Estoque', $estoque->id_Estoque)
                    ->update([
                        'qtd_Estoque' => DB::raw('qtd_Estoque - ' . $validated['qtd_Saida']),
                        'updated_at' => now()
                    ]);

                DB::commit();

                return response()->json([
                    'status' => 'sucesso',
                    'mensagem' => 'Saída de produto registrada com sucesso.',
                    'id_Saida' => $idSaida
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao registrar saída de produto: ' . $e->getMessage());
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Erro ao registrar saída: ' . $e->getMessage()
            ]);
        }
    }

    public function search(Request $request)
    {
        try {
            $termo = $request->input('termo');

            $query = DB::table('saida_produtos')
                ->leftJoin('local_destinos', 'saida_produtos.id_Local', '=', 'local_destinos.id_Local')
                ->select(
                    'saida_produtos.*',
                    'local_destinos.tipo_Local'
                );

            if (!empty($termo)) {
                $query->where(function ($q) use ($termo) {
                    $q->where('saida_produtos.cod_Produto', 'LIKE', '%' . $termo . '%')
                      ->orWhere('saida_produtos.nome_Produto', 'LIKE', '%' . $termo . '%')
                      ->orWhere('saida_produtos.nome_Local', 'LIKE', '%' . $termo . '%');
                });
            }

            $saidas = $query->orderBy('saida_produtos.data_Saida', 'desc')->get();

            if ($saidas->isEmpty()) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Nenhuma saída de produto encontrada.'
                ]);
            }

            // Processar imagens em base64
            foreach ($saidas as $saida) {
                if ($saida->imagem) {
                    $saida->imagem = base64_encode($saida->imagem);
                }
            }

            return response()->json([
                'status' => 'sucesso',
                'saidas' => $saidas
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar saídas de produtos: ' . $e->getMessage());

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
                'id_Saida' => 'required|integer',
            ]);
            
            $id_Saida = $request->input('id_Saida');

            // Verificar se a saída existe
            $saida = DB::table('saida_produtos')->where('id_Saida', $id_Saida)->first();
            
            if (!$saida) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Registro de saída não encontrado.'
                ]);
            }

            // Iniciar transação
            DB::beginTransaction();

            try {
                // Restaurar quantidade ao estoque
                DB::table('estoques')
                    ->where('id_Estoque', $saida->id_Estoque)
                    ->update([
                        'qtd_Estoque' => DB::raw('qtd_Estoque + ' . $saida->qtd_Saida),
                        'updated_at' => now()
                    ]);

                // Excluir o registro de saída
                DB::table('saida_produtos')->where('id_Saida', $id_Saida)->delete();

                DB::commit();

                return response()->json([
                    'status' => 'sucesso',
                    'mensagem' => 'Saída de produto excluída com sucesso.'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir saída de produto: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao excluir a saída de produto: ' . $e->getMessage()
            ]);
        }
    }

    public function find(Request $request)
    {
        try {
            $id = $request->input('id_Saida');
            
            if (!$id) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'ID da saída não fornecido.'
                ]);
            }

            $saida = DB::table('saida_produtos')
                ->leftJoin('local_destinos', 'saida_produtos.id_Local', '=', 'local_destinos.id_Local')
                ->where('saida_produtos.id_Saida', $id)
                ->select(
                    'saida_produtos.*',
                    'local_destinos.tipo_Local'
                )
                ->first();

            if (!$saida) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Saída de produto não encontrada.'
                ]);
            }

            // Processar imagem em base64
            if ($saida->imagem) {
                $saida->imagem = base64_encode($saida->imagem);
            }

            return response()->json([
                'status' => 'sucesso',
                'saida' => $saida
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao buscar detalhes da saída de produto: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao buscar os detalhes da saída de produto: ' . $e->getMessage()
            ]);
        }
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_Saida' => 'required|integer',
                'id_Local' => 'required|integer',
                'qtd_Saida' => 'required|numeric|min:0.01',
                'observacao' => 'nullable|string|max:120',
                'data_Saida' => 'required|date',
            ]);

            // Verificar se a saída existe
            $saida = DB::table('saida_produtos')
                ->where('id_Saida', $validated['id_Saida'])
                ->first();

            if (!$saida) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Registro de saída não encontrado.'
                ]);
            }

            // Verificar se o local de destino existe
            $localDestino = DB::table('local_destinos')
                ->where('id_Local', $validated['id_Local'])
                ->first();

            if (!$localDestino) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Local de destino não encontrado.'
                ]);
            }

            // Verificar estoque para nova quantidade
            $estoque = DB::table('estoques')
                ->where('id_Estoque', $saida->id_Estoque)
                ->first();
            
            if (!$estoque) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Registro de estoque não encontrado.'
                ]);
            }
            
            // Calcular a diferença de quantidade
            $diferenca = $validated['qtd_Saida'] - $saida->qtd_Saida;
            
            // Verificar disponibilidade no estoque se a nova quantidade for maior
            if ($diferenca > 0 && $estoque->qtd_Estoque < $diferenca) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Estoque insuficiente para este aumento de quantidade. Disponível: ' . $estoque->qtd_Estoque
                ]);
            }

            // Calcular novo valor total
            $valorTotal = $saida->preco_Custo * $validated['qtd_Saida'];

            // Atualizar registro
            DB::beginTransaction();
            try {
                // Atualizar registro de saída
                DB::table('saida_produtos')
                    ->where('id_Saida', $validated['id_Saida'])
                    ->update([
                        'id_Local' => $localDestino->id_Local,
                        'nome_Local' => $localDestino->nome_Local,
                        'qtd_Saida' => $validated['qtd_Saida'],
                        'valor_Total' => $valorTotal,
                        'observacao' => $validated['observacao'],
                        'data_Saida' => $validated['data_Saida'],
                        'updated_at' => now()
                    ]);

                // Atualizar estoque de acordo com a diferença
                if ($diferenca != 0) {
                    DB::table('estoques')
                        ->where('id_Estoque', $saida->id_Estoque)
                        ->update([
                            'qtd_Estoque' => DB::raw('qtd_Estoque - ' . $diferenca),
                            'updated_at' => now()
                        ]);
                }

                DB::commit();

                return response()->json([
                    'status' => 'sucesso',
                    'mensagem' => 'Saída de produto atualizada com sucesso.'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar saída de produto: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Ocorreu um erro ao atualizar a saída de produto: ' . $e->getMessage()
            ]);
        }
    }

    // ...existing code...
}