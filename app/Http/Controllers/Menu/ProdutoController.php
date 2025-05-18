<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Menu\Produto;
use function PHPUnit\Framework\returnArgument;

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

        if (Produto::where('cod_Produto', $validated['cod_Produto'])->exists()) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Este produto já está cadastrado no sistema.'
            ]);
        }

        $produtoData = $validated;
        if ($request->hasFile('imagem')) {
            $produtoData['imagem'] = file_get_contents($request->file('imagem')->getPathName());
        }

        $produto = Produto::create($produtoData);
        // Cria estoque zerado
        DB::table('estoques')->insert([
            'cod_Produto' => $produto->cod_Produto,
            'qtd_Estoque' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'status' => 'sucesso',
            'mensagem' => 'Produto cadastrado com sucesso!'
        ]);
    }

    public function search(Request $request)
    {
        //verifica se nenhum filtro foi enviado na requisição
        if (!$request->filled('cod_Produto', 'nome_Produto', 'grupo', 'sub_Grupo')){
            //retorna os 20 produtos com o maior código
            $produtos = Produto::orderBy('cod_Produto', 'desc')
                ->limit(20)
                ->get();

                foreach ($produtos as $produto){
                    if ($produto ->imagem){
                        $produto->imagem = base64_encode($produto->imagem);
                    }
                }

                return response()->json([
                    'status' => 'sucesso',
                    'mensagem' => 'Sem filtros: exibindo os 20 produtos mais recentes.',
                    'produtos' => $produtos
                ]);
        }

        //Caso tenha filtros, monta a query dinamicamente
        $query = Produto::query();

        if ($request->filled('cod_Produto')) {
            $query->where('cod_Produto', 'like', "%{$request->cod_Produto}%");
        }
        if ($request->filled('nome_Produto')) {
            $query->where('nome_Produto', 'like', "%{$request->nome_Produto}%");
        }
        if ($request->filled('grupo')) {
            $query->where('grupo', 'like', "%{$request->grupo}%");
        }
        if ($request->filled('sub_Grupo')) {
            $query->where('sub_Grupo', 'like', "%{$request->sub_Grupo}%");
        }
        $produtos = $query->orderBy('nome_Produto', 'asc')->get();
        foreach ($produtos as $produto) {
            if ($produto->imagem) {
                $produto->imagem = base64_encode($produto->imagem);
            }
        }
        return response()->json([
            'status' => 'sucesso',
            'produtos' => $produtos
        ]);
    }

    public function ultimoCodigo(){
        $ultimo = Produto::orderBy('cod_Produto', 'desc')->first();

        if (!$ultimo) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Nenhum produto encontrado.'
            ], 404);
        }

        return response()->json([
            'status' => 'sucesso',
            'cod_Produto' => $ultimo->cod_Produto
        ]);
    }

    public function find(Request $request)
    {
        $validated = $request->validate([
            'cod_Produto' => 'required|integer',
        ]);
        $produto = Produto::find($validated['cod_Produto']);
        if ($produto) {
            if ($produto->imagem) {
                $produto->imagem = base64_encode($produto->imagem);
            }
            return response()->json([
                'status' => 'sucesso',
                'produto' => $produto
            ]);
        } else {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Produto não encontrado.'
            ]);
        }
    }

    public function update(Request $request)
    {
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
        $produto = Produto::find($validated['cod_Produto']);
        if (!$produto) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Produto não encontrado.'
            ]);
        }
        $updateData = $validated;
        if ($request->hasFile('imagem')) {
            $updateData['imagem'] = file_get_contents($request->file('imagem')->getPathName());
        } else {
            unset($updateData['imagem']);
        }
        $produto->update($updateData);
        return response()->json([
            'status' => 'sucesso',
            'mensagem' => 'Produto atualizado com sucesso!'
        ]);
    }

    public function destroy(Request $request)
    {
        $codProduto = $request->input('cod_Produto');
        $produto = Produto::find($codProduto);
        if (!$produto) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Produto não encontrado.'
            ]);
        }
        $temEntradas = DB::table('entrada_produtos')->where('cod_Produto', $codProduto)->exists();
        $temSaidas = DB::table('saida_produtos')->where('cod_Produto', $codProduto)->exists();
        if ($temEntradas || $temSaidas) {
            return response()->json([
                'status' => 'erro',
                'mensagem' => 'Não é possível excluir este produto pois ele possui movimentações no sistema.'
            ]);
        }
        DB::table('estoques')->where('cod_Produto', $codProduto)->delete();
        $produto->delete();
        return response()->json([
            'status' => 'sucesso',
            'mensagem' => 'Produto excluído com sucesso!'
        ]);
    }
}
