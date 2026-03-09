<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Estudante;
use App\Models\Explicador;

class AuthController extends Controller
{
    /**
     * Login do usuário
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'tipo' => 'required|in:estudante,explicador,admin'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();

        // Verificar se o tipo corresponde
        if ($user->tipo !== $request->tipo) {
            Auth::logout();
            return response()->json([
                'message' => 'Tipo de usuário inválido'
            ], 403);
        }

        // Verificar status do usuário
        if ($user->status !== 'Ativo') {
            Auth::logout();
            return response()->json([
                'message' => 'Usuário ' . strtolower($user->status)
            ], 403);
        }

        // Atualizar último acesso
        $user->update(['ultimo_acesso' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // Carregar dados adicionais conforme o tipo
        if ($user->tipo === 'explicador') {
            $user->load('explicador');
        } elseif ($user->tipo === 'estudante') {
            $user->load('estudante');
        }

        return response()->json([
            'message' => 'Login realizado com sucesso',
            'user' => $user,
            'token' => $token,
            'tipo' => $user->tipo
        ]);
    }

    /**
     * Registro de novo usuário
     */
    public function register(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'tipo' => 'required|in:estudante,explicador',
            'telefone' => 'required|string',
            'nivel' => 'required_if:tipo,estudante|string',
            'instituicao' => 'nullable|string',
            'materias' => 'required_if:tipo,explicador|array',
            'descricao' => 'nullable|string'
        ]);

        // Criar usuário
        $user = User::create([
            'nome' => $request->nome,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tipo' => $request->tipo,
            'telefone' => $request->telefone,
            'status' => $request->tipo === 'explicador' ? 'Pendente' : 'Ativo',
            'avatar' => 'https://cdn.quasar.dev/img/avatar' . rand(1,6) . '.jpg'
        ]);

        // Criar perfil específico
        if ($request->tipo === 'estudante') {
            Estudante::create([
                'user_id' => $user->id,
                'nivel' => $request->nivel,
                'instituicao' => $request->instituicao,
                'pedidos_realizados' => 0,
                'sessoes_realizadas' => 0,
                'saldo' => 0
            ]);
        } else {
            Explicador::create([
                'user_id' => $user->id,
                'materias' => $request->materias,
                'descricao' => $request->descricao,
                'avaliacao' => 0,
                'total_avaliacoes' => 0,
                'sessoes_realizadas' => 0,
                'estudantes_atendidos' => 0,
                'taxa_aceitacao' => 0,
                'preco_medio' => 0,
                'status' => 'Pendente'
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Cadastro realizado com sucesso',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout realizado com sucesso'
        ]);
    }

    /**
     * Usuário atual
     */
    public function me(Request $request)
    {
        $user = $request->user();

        if ($user->tipo === 'explicador') {
            $user->load('explicador');
        } elseif ($user->tipo === 'estudante') {
            $user->load('estudante');
        }

        return response()->json($user);
    }

    /**
     * Atualizar perfil
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'nome' => 'sometimes|string|max:255',
            'telefone' => 'sometimes|string',
            'avatar' => 'sometimes|string'
        ]);

        $user->update($request->only(['nome', 'telefone', 'avatar']));

        return response()->json([
            'message' => 'Perfil atualizado',
            'user' => $user
        ]);
    }

    /**
     * Alterar senha
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed'
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Senha atual incorreta'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Senha alterada com sucesso'
        ]);
    }
}
