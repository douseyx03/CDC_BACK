<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAgentRequest;
use App\Http\Requests\UpdateAgentRequest;
use App\Models\Agent;
use App\Services\Agent\AgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function __construct(private readonly AgentService $agentService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $agents = Agent::query()
            ->with(['user.roles'])
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return response()->json($agents);
    }

    public function store(StoreAgentRequest $request): JsonResponse
    {
        $agent = $this->agentService->create($request->validated());

        return response()->json($agent, 201);
    }

    public function show(Agent $agent): JsonResponse
    {
        return response()->json($agent->load(['user.roles']));
    }

    public function update(UpdateAgentRequest $request, Agent $agent): JsonResponse
    {
        $agent = $this->agentService->update($agent, $request->validated());

        return response()->json($agent);
    }

    public function destroy(Agent $agent): JsonResponse
    {
        $this->agentService->delete($agent);

        return response()->json(null, 204);
    }
}
