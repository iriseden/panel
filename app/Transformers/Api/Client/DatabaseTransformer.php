<?php

namespace App\Transformers\Api\Client;

use App\Models\Database;
use League\Fractal\Resource\Item;
use App\Models\Permission;
use League\Fractal\Resource\NullResource;
use App\Contracts\Extensions\HashidsInterface;

class DatabaseTransformer extends BaseClientTransformer
{
    protected array $availableIncludes = ['password'];

    private HashidsInterface $hashids;

    /**
     * Handle dependency injection.
     */
    public function handle(HashidsInterface $hashids)
    {
        $this->hashids = $hashids;
    }

    public function getResourceName(): string
    {
        return Database::RESOURCE_NAME;
    }

    public function transform(Database $model): array
    {
        $model->loadMissing('host');

        return [
            'id' => $this->hashids->encode($model->id),
            'host' => [
                'address' => $model->getRelation('host')->host,
                'port' => $model->getRelation('host')->port,
            ],
            'name' => $model->database,
            'username' => $model->username,
            'connections_from' => $model->remote,
            'max_connections' => $model->max_connections,
        ];
    }

    /**
     * Include the database password in the request.
     */
    public function includePassword(Database $database): Item|NullResource
    {
        if (!$this->request->user()->can(Permission::ACTION_DATABASE_VIEW_PASSWORD, $database->server)) {
            return $this->null();
        }

        return $this->item($database, function (Database $model) {
            return [
                'password' => $model->password,
            ];
        }, 'database_password');
    }
}
