<?php

namespace App\Transformers\Api\Application;

use App\Models\Node;
use App\Models\Database;
use App\Models\DatabaseHost;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\NullResource;

class DatabaseHostTransformer extends BaseTransformer
{
    protected array $availableIncludes = [
        'databases',
        'node',
    ];

    /**
     * Return the resource name for the JSONAPI output.
     */
    public function getResourceName(): string
    {
        return DatabaseHost::RESOURCE_NAME;
    }

    /**
     * Transform database host into a representation for the application API.
     */
    public function transform(DatabaseHost $model): array
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'host' => $model->host,
            'port' => $model->port,
            'username' => $model->username,
            'node' => $model->node_id,
            'created_at' => $model->created_at->toAtomString(),
            'updated_at' => $model->updated_at->toAtomString(),
        ];
    }

    /**
     * Include the databases associated with this host.
     */
    public function includeDatabases(DatabaseHost $model): Collection|NullResource
    {
        if (!$this->authorize(Database::RESOURCE_NAME)) {
            return $this->null();
        }

        $model->loadMissing('databases');

        return $this->collection($model->getRelation('databases'), $this->makeTransformer(ServerDatabaseTransformer::class), Database::RESOURCE_NAME);
    }

    /**
     * Include the node associated with this host.
     */
    public function includeNode(DatabaseHost $model): Item|NullResource
    {
        if (!$this->authorize(Node::RESOURCE_NAME)) {
            return $this->null();
        }

        $model->loadMissing('node');

        return $this->item($model->getRelation('node'), $this->makeTransformer(NodeTransformer::class), Node::RESOURCE_NAME);
    }
}
