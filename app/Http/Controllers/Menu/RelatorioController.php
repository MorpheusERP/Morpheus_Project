<?php

namespace App\Http\Controllers\Menu;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Relatorio\Usuario;
use App\Models\Relatorio\Fornecedor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RelatorioController extends Controller
{
    public function index()
    {
        return view('menu.relatorio.relatorio');
    }
    
    public function usuariosRelatorio()
    {
        return view('menu.relatorio.usuario-relatorio');
    }

    public function searchUsuarios(Request $request)
    {
        try {
            Log::info('Received search request:', $request->all());
            
            $request->validate([
                'nome' => 'nullable|string|max:255',
                'funcao' => 'nullable|string|max:255',
            ]);

            $query = Usuario::query();

            if ($request->filled('nome')) {
                $query->where('nome_Usuario', 'like', '%' . $request->nome . '%');
            }

            if ($request->filled('funcao')) {
                if (in_array('funcao', $query->getModel()->getFillable())) {
                    $query->where('funcao', 'like', '%' . $request->funcao . '%');
                } elseif (in_array('tipo_Usuario', $query->getModel()->getFillable())) {
                    $query->where('tipo_Usuario', 'like', '%' . $request->funcao . '%');
                } elseif (in_array('nivel_Usuario', $query->getModel()->getFillable())) {
                    $query->where('nivel_Usuario', 'like', '%' . $request->funcao . '%');
                }
            }

            if (!$request->filled('nome') && !$request->filled('funcao')) {
                $query->limit(100);
            }

            $usuarios = $query->get();

            return response()->json([
                'status' => 'success',
                'usuarios' => $usuarios
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchUsuarios: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar a consulta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function fornecedoresRelatorio()
    {
        return view('menu.relatorio.fornecedor-relatorio');
    }

    public function searchFornecedores(Request $request)
    {
        try {
            Log::info('Received fornecedores search request:', $request->all());
            
            $request->validate([
                'razao_Social' => 'nullable|string|max:255',
                'nome_Fantasia' => 'nullable|string|max:255',
                'grupo' => 'nullable|string|max:255',
                'sub_Grupo' => 'nullable|string|max:255',
            ]);

            $query = DB::table('fornecedores');

            if ($request->filled('razao_Social')) {
                $query->where('razao_Social', 'like', '%' . $request->razao_Social . '%');
            }

            if ($request->filled('nome_Fantasia')) {
                $query->where('nome_Fantasia', 'like', '%' . $request->nome_Fantasia . '%');
            }

            if ($request->filled('grupo')) {
                $query->where('grupo', 'like', '%' . $request->grupo . '%');
            }

            if ($request->filled('sub_Grupo')) {
                $query->where('sub_Grupo', 'like', '%' . $request->sub_Grupo . '%');
            }

            if (!$request->filled('razao_Social') && !$request->filled('nome_Fantasia') && 
                !$request->filled('grupo') && !$request->filled('sub_Grupo')) {
                $query->limit(100);
            }

            $fornecedores = $query->get();

            return response()->json([
                'status' => 'success',
                'fornecedores' => $fornecedores
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchFornecedores: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar a consulta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function locaisRelatorio()
    {
        return view('menu.relatorio.local-destino-relatorio');
    }

    public function searchLocais(Request $request)
    {
        try {
            Log::info('Received locais search request:', $request->all());
            
            $request->validate([
                'nome_Local' => 'nullable|string|max:255',
                'tipo_Local' => 'nullable|string|max:255',
            ]);

            $query = DB::table('local_destinos');

            if ($request->filled('nome_Local')) {
                $query->where('nome_Local', 'like', '%' . $request->nome_Local . '%');
            }

            if ($request->filled('tipo_Local')) {
                $query->where('tipo_Local', 'like', '%' . $request->tipo_Local . '%');
            }

            if (!$request->filled('nome_Local') && !$request->filled('tipo_Local')) {
                $query->limit(100);
            }

            $locais = $query->get();

            return response()->json([
                'status' => 'success',
                'locais' => $locais
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchLocais: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar a consulta: ' . $e->getMessage()
            ], 500);
        }
    }

    public function produtosRelatorio()
    {
        return view('menu.relatorio.produtos-relatorio');
    }

    public function searchProdutos(Request $request)
    {
        try {
            Log::info('Received produtos search request:', $request->all());
            
            $request->validate([
                'cod_Produto' => 'nullable|numeric',
                'nome_Produto' => 'nullable|string|max:255',
                'grupo' => 'nullable|string|max:255',
                'sub_Grupo' => 'nullable|string|max:255',
            ]);

            $query = DB::table('produtos');

            if ($request->filled('cod_Produto')) {
                $query->where('cod_Produto', $request->cod_Produto);
            }

            if ($request->filled('nome_Produto')) {
                // Use UTF-8 safe comparison for text fields
                $query->whereRaw('LOWER(nome_Produto) LIKE ?', ['%' . mb_strtolower($request->nome_Produto, 'UTF-8') . '%']);
            }

            if ($request->filled('grupo')) {
                // Use UTF-8 safe comparison for text fields
                $query->whereRaw('LOWER(grupo) LIKE ?', ['%' . mb_strtolower($request->grupo, 'UTF-8') . '%']);
            }

            if ($request->filled('sub_Grupo')) {
                // Use UTF-8 safe comparison for text fields
                $query->whereRaw('LOWER(sub_Grupo) LIKE ?', ['%' . mb_strtolower($request->sub_Grupo, 'UTF-8') . '%']);
            }

            // If no search criteria provided, limit to 100 products
            if (!$request->filled('cod_Produto') && !$request->filled('nome_Produto') && 
                !$request->filled('grupo') && !$request->filled('sub_Grupo')) {
                $query->limit(100);
            }

            // Get products with proper encoding
            $produtos = $query->get()->map(function($produto) {
                // Convert any binary data to UTF-8 safely
                foreach ($produto as $key => $value) {
                    if (is_string($value)) {
                        $produto->$key = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    }
                }
                return $produto;
            });

            Log::info('Found ' . count($produtos) . ' produtos');

            return response()->json([
                'status' => 'success',
                'produtos' => $produtos
            ], 200, ['Content-Type' => 'application/json;charset=UTF-8']);
        } catch (\Exception $e) {
            Log::error('Error in searchProdutos: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar a consulta: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function entradasRelatorio()
    {
        return view('menu.relatorio.entrada-produto-relatorio');
    }
    
    public function searchEntradas(Request $request)
    {
        try {
            Log::info('Received entradas search request:', $request->all());
            
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'nome_Produto' => 'nullable|string|max:255',
                'razao_Social' => 'nullable|string|max:255',
                'grupo' => 'nullable|string|max:255',
                'sub_Grupo' => 'nullable|string|max:255',
            ]);
            
            // Consulta base para obter as entradas dentro do período especificado
            // Usando id_Entrada em vez de id_Lote que não existe
            $query = DB::table('entrada_produtos')
                ->select(
                    'entrada_produtos.id_Entrada',
                    DB::raw('MAX(entrada_produtos.data_Entrada) as data_Entrada'),
                    DB::raw('SUM(entrada_produtos.preco_Custo * entrada_produtos.qtd_Entrada) as valor_Total')
                )
                ->whereBetween('data_Entrada', [$request->start_date, $request->end_date])
                ->groupBy('entrada_produtos.id_Entrada');
            
            // Se tiver critérios adicionais de busca, vamos filtrar usando join com produtos
            if ($request->filled('nome_Produto') || $request->filled('grupo') || $request->filled('sub_Grupo') || $request->filled('razao_Social')) {
                $query->join('produtos', 'entrada_produtos.cod_Produto', '=', 'produtos.cod_Produto');
                
                // Filtrar por nome do produto
                if ($request->filled('nome_Produto')) {
                    $query->where('produtos.nome_Produto', 'like', '%' . $request->nome_Produto . '%');
                }
                
                // Filtrar por grupo
                if ($request->filled('grupo')) {
                    $query->where('produtos.grupo', 'like', '%' . $request->grupo . '%');
                }
                
                // Filtrar por subgrupo
                if ($request->filled('sub_Grupo')) {
                    $query->where('produtos.sub_Grupo', 'like', '%' . $request->sub_Grupo . '%');
                }
                
                // Filtrar por fornecedor
                if ($request->filled('razao_Social')) {
                    $query->where('entrada_produtos.razao_Social', 'like', '%' . $request->razao_Social . '%');
                }
            }
            
            // Obter os resultados
            $entradas = $query->get();
            
            Log::info('Found ' . count($entradas) . ' entradas de produtos');
            
            return response()->json([
                'status' => 'success',
                'entradas' => $entradas
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchEntradas: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar a consulta: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function detalhesEntrada(Request $request)
    {
        try {
            Log::info('Received entrada details request:', $request->all());
            
            $request->validate([
                'id_Entrada' => 'required|integer',
            ]);
            
            // Consulta para obter os produtos da entrada específica com informações do fornecedor
            $produtos = DB::table('entrada_produtos')
                ->join('produtos', 'entrada_produtos.cod_Produto', '=', 'produtos.cod_Produto')
                ->where('entrada_produtos.id_Entrada', $request->id_Entrada)
                ->select(
                    'entrada_produtos.*',
                    'produtos.nome_Produto',
                    'produtos.grupo',
                    'produtos.sub_Grupo'
                )
                ->get();
            
            if ($produtos->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Nenhum produto encontrado para esta entrada'
                ]);
            }
            
            Log::info('Found ' . count($produtos) . ' produtos for entrada ID: ' . $request->id_Entrada);
            
            return response()->json([
                'status' => 'success',
                'produtos' => $produtos
            ]);
        } catch (\Exception $e) {
            Log::error('Error in detalhesEntrada: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao processar a consulta: ' . $e->getMessage()
            ], 500);
        }
    }
}
