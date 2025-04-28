<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProdutoController extends Controller
{
    public function index()
    {
        return view('menu.produtos.produtos');
    }

    public function showBuscar()
    {
        return view('menu.produtos.produtos-buscar');
    }
    public function showEditar()
    {
        return view('menu.produtos.produtos-editar');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'cod_Produto' => 'required|integer',
                'imagem' => 'nullable|image|max:2048',
                'nome_Produto' => 'required|string|max:150',
                'tipo_Produto' => 'required|string|max:50',
                'cod_Barras' => 'nullable|integer',
                'preco_Custo' => 'required|numeric',
                'preco_Venda' => 'nullable|numeric',
                'grupo' => 'required|string|max:50',
                'sub_Grupo' => 'nullable|string|max:50',
                'observacao' => 'nullable|string|max:150',
            ]);

            // Verifica se o produto já está cadastrado
            $existingProduto = DB::table('produtos')
                ->where('cod_Produto', $validated['cod_Produto'])
                ->first();

            if ($existingProduto) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Este produto já está cadastrado no sistema.'
                ]);
            }

            // Preparar dados para inserção
            $produtoData = [
                'cod_Produto' => $validated['cod_Produto'],
                'nome_Produto' => $validated['nome_Produto'],
                'tipo_Produto' => $validated['tipo_Produto'],
                'cod_Barras' => $validated['cod_Barras'] ?? null,
                'preco_Custo' => $validated['preco_Custo'],
                'preco_Venda' => $validated['preco_Venda'] ?? null,
                'grupo' => $validated['grupo'],
                'sub_Grupo' => $validated['sub_Grupo'] ?? null,
                'observacao' => $validated['observacao'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ];

            // Processar imagem se fornecida
            if ($request->hasFile('imagem')) {
                try {
                    $produtoData['imagem'] = file_get_contents($request->file('imagem')->getPathName());
                } catch (\Exception $e) {
                    Log::error('Erro ao processar imagem: '.$e->getMessage());
                    return response()->json([
                        'status' => 'erro',
                        'mensagem' => 'Erro ao processar imagem: ' . $e->getMessage()
                    ]);
                }
            }

            // Inserir produto no banco de dados
            DB::beginTransaction();
            try {
                DB::table('produtos')->insert($produtoData);

                // Criar entrada no estoque com quantidade zero
                DB::table('estoques')->insert([
                    'cod_Produto' => $validated['cod_Produto'],
                    'qtd_Estoque' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                DB::commit();
                return response()->json([
                    'status' => 'sucesso',
                    'mensagem' => 'Produto cadastrado com sucesso!'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erro ao inserir produto ou estoque: '.$e->getMessage());
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erro de validação: '.json_encode($e->errors()));
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Erro de validação: ' . implode(' ', array_map(function($arr) { return implode(' ', $arr); }, $e->errors()))
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao cadastrar produto: '.$e->getMessage());
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Erro ao cadastrar produto: ' . $e->getMessage()
            ]);
        }
    }

    public function search(Request $request)
    {
        try {
            $termo = $request->query('termo');
            
            $query = DB::table('produtos')
                ->leftJoin('estoques', 'produtos.cod_Produto', '=', 'estoques.cod_Produto')
                ->select('produtos.*', 'estoques.qtd_Estoque');
            
            if (!empty($termo)) {
                $query->where(function($q) use ($termo) {
                    $q->where('produtos.cod_Produto', 'like', "%{$termo}%")
                      ->orWhere('produtos.nome_Produto', 'like', "%{$termo}%")
                      ->orWhere('produtos.grupo', 'like', "%{$termo}%");
                });
            }
            
            $produtos = $query->orderBy('produtos.nome_Produto', 'asc')->get();
            
            // Converter imagens para base64
            foreach ($produtos as $produto) {
                if ($produto->imagem) {
                    $produto->imagem = base64_encode($produto->imagem);
                }
            }
            
            return response()->json([
                'status' => 'sucesso',
                'produtos' => $produtos
            ]);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar produtos: ' . $e->getMessage());
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Erro ao buscar produtos: ' . $e->getMessage()
            ], 500);
        }
    }

    public function find(Request $request)
    {
        try {
            $validated = $request->validate([
                'cod_Produto' => 'required|integer',
            ]);

            $produto = DB::table('produtos')
                ->where('cod_Produto', $validated['cod_Produto'])
                ->first();

            if ($produto) {
                // Verifica se há imagem e converte para base64 se necessário
                if (property_exists($produto, 'imagem') && $produto->imagem) {
                    $produto->imagem = base64_encode($produto->imagem);
                }
                
                // Certifica-se de que todos os campos de texto estão codificados corretamente em UTF-8
                foreach ($produto as $key => $value) {
                    if (is_string($value)) {
                        $produto->$key = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    }
                }

                return response()->json([
                    'status' => 'sucesso',
                    'produto' => $produto
                ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
            } else {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Produto não encontrado.'
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erro de validação ao buscar produto: ' . json_encode($e->errors()));
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Erro de validação: ' . implode(' ', array_map(function($arr) { return implode(' ', $arr); }, $e->errors()))
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar produto: ' . $e->getMessage());
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Erro ao buscar produto. Tente novamente mais tarde.'
            ]);
        }
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'cod_Produto' => 'required|integer',
                'imagem' => 'nullable|image|max:2048',
                'nome_Produto' => 'required|string|max:150',
                'tipo_Produto' => 'required|string|max:50',
                'cod_Barras' => 'nullable|integer',
                'preco_Custo' => 'required|numeric',
                'preco_Venda' => 'nullable|numeric',
                'grupo' => 'required|string|max:50',
                'sub_Grupo' => 'nullable|string|max:50',
                'observacao' => 'nullable|string|max:150',
            ]);

            // Verificar se o produto existe
            $produto = DB::table('produtos')
                ->where('cod_Produto', $validated['cod_Produto'])
                ->first();

            if (!$produto) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Produto não encontrado.'
                ]);
            }

            // Preparar os dados para atualização
            $updateData = [
                'nome_Produto' => $validated['nome_Produto'],
                'tipo_Produto' => $validated['tipo_Produto'],
                'cod_Barras' => $validated['cod_Barras'] ?? null,
                'preco_Custo' => $validated['preco_Custo'],
                'preco_Venda' => $validated['preco_Venda'] ?? null,
                'grupo' => $validated['grupo'],
                'sub_Grupo' => $validated['sub_Grupo'] ?? null,
                'observacao' => $validated['observacao'] ?? null,
                'updated_at' => now()
            ];

            // Verificar se uma nova imagem foi enviada
            if ($request->hasFile('imagem')) {
                $updateData['imagem'] = file_get_contents($request->file('imagem')->getPathName());
            }

            DB::table('produtos')
                ->where('cod_Produto', $validated['cod_Produto'])
                ->update($updateData);

            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Produto atualizado com sucesso!'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar produto: '.$e->getMessage());
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Erro ao atualizar produto: ' . $e->getMessage()
            ]);
        }
    }
    public function destroy(Request $request)
    {
        try {
            $codProduto = $request->input('cod_Produto');
            
            // Verificar se o produto existe
            $produto = DB::table('produtos')
                ->where('cod_Produto', $codProduto)
                ->first();

            if (!$produto) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Produto não encontrado.'
                ]);
            }
            
            // Verificar se há entradas ou saídas relacionadas a este produto
            $temEntradas = DB::table('entrada_produtos')
                ->where('cod_Produto', $codProduto)
                ->exists();
                
            $temSaidas = DB::table('saida_produtos')
                ->where('cod_Produto', $codProduto)
                ->exists();
                
            if ($temEntradas || $temSaidas) {
                return response()->json([
                    'status' => 'erro',
                    'mensagem' => 'Não é possível excluir este produto pois ele possui movimentações no sistema.'
                ]);
            }
            
            // Excluir registro no estoque primeiro (devido à chave estrangeira)
            DB::table('estoques')
                ->where('cod_Produto', $codProduto)
                ->delete();
            
            // Depois excluir o produto
            DB::table('produtos')
                ->where('cod_Produto', $codProduto)
                ->delete();

            return response()->json([
                'status' => 'sucesso',
                'mensagem' => 'Produto excluído com sucesso!'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir produto: '.$e->getMessage());
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Erro ao excluir produto: ' . $e->getMessage()
            ]);
        }
    }
}
