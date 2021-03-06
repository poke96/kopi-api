<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 7/11/2017
 * Time: 5:08 PM
 */

namespace App\Repositories;

use App\Models\Order;
use App\Models\Product;
use App\Repositories\Contracts\OrderContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository implements OrderContract
{
    /**
     * @var Connection
     */
    private $db;

    /**
     * OrderRepository constructor.
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Create Order
     * @param int $userId
     * @param array $products
     * @return Order
     */
    public function create(int $userId, array $products): Order
    {
        $this->db->beginTransaction();
        $order = new Order;
        $order->user_id = $userId;
        $order->save();
        $dataProduct = [];
        foreach ($products as $p) {
            $entity = Product::find($p["id"]);
            $dataProduct[$p["id"]] = [
                "quantity" => $p["quantity"],
                "price" => $entity->price
            ];
            if($entity->recipe == null) {
                $this->db->table('product_user')->where([
                    'user_id' => $userId,
                    'product_id' => $p["id"]
                ])->decrement("quantity", $p["quantity"]);
            }
            else {
                foreach($entity->recipe as $key => $value) {
                    $this->db->table('product_user')->where([
                        'user_id' => $userId,
                        'product_id' => $key
                    ])->decrement("quantity", $p["quantity"] * $value);
                }
            }
        }
        $order->products()->attach($dataProduct);
        $this->db->commit();
        return $order;
    }

    public function getProducts(int $id): Collection
    {
        return Order::findorfail($id)->products;
    }

    public function getProductsPaginate(int $id, int $limit = 15): LengthAwarePaginator
    {
        return Order::findorfail($id)->products()->paginate($limit);
    }

    public function getDetail(int $id): Order
    {
        return Order::findorfail($id);
    }
}