<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 7/19/2017
 * Time: 9:10 PM
 */

namespace App\Repositories;

use App\Models\Product;
use App\Models\Request;
use App\Repositories\Contracts\RequestContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RequestRepository implements RequestContract
{
    /**
     * @var Connection
     */
    private $db;

    private $request;

    /**
     * OrderRepository constructor.
     * @param Connection $db
     */
    public function __construct(Connection $db, Request $request)
    {
        $this->db = $db;
        $this->request = $request->newQuery();
    }

    /**
     * Create Request
     * @param int $userId
     * @param array $products
     * @return Request
     */
    public function create(int $userId, array $products): Request
    {
        $this->db->beginTransaction();
        $request = new Request;
        $request->user_id = $userId;
        $request->save();
        $dataProduct = [];
        foreach ($products as $p) {
            $dataProduct[$p["id"]] = [
                "quantity" => $p["quantity"],
            ];
        }
        $request->products()->attach($dataProduct);
        $this->db->commit();
        return $request;
    }

    public function checkMinStock(int $productId, int $quantity)
    {
        $product = Product::find($productId);
        if($product->min_stock == null)
            return true;
        if($quantity >= $product->min_stock)
            return true;
        else
            return false;
    }

    public function getAll($filter = '0', bool $isDone = false): Collection
    {
        $query = $this->request;
        if($filter)
            $query->where('status', $isDone);
        return $this->request->get();
    }

    public function getPaginate($filter = '0', bool $isDone = false, int $limit = 15): LengthAwarePaginator
    {
        $query = $this->request;
        if($filter)
            $query->where('status', $isDone);
        return $this->request->paginate();
    }

    public function getProducts(int $id)
    {
        return DB::table('request_product as rp')
            ->join('requests as r', 'rp.request_id', '=', 'r.id')
            ->join('product_user as pu', function ($join) {
                $join->on('pu.user_id', '=', 'r.user_id')
                    ->on('pu.product_id', '=','rp.product_id');
            })
            ->join('products as p', 'p.id', '=', 'rp.product_id')
            ->select('rp.*', 'pu.quantity AS user_stock', 'p.name', 'p.image_url', 'p.purchase_price', 'p.price', 'p.id', 'p.min_stock_unit', 'p.type')
            ->where('rp.request_id', $id)->get();
    }

    public function getProductsPaginate(int $id, int $limit = 15): LengthAwarePaginator
    {
        return DB::table('request_product as rp')
            ->join('requests as r', 'rp.request_id', '=', 'r.id')
            ->join('product_user as pu', function ($join) {
                $join->on('pu.user_id', '=', 'r.user_id')
                    ->on('pu.product_id', '=','rp.product_id');
            })
            ->join('products as p', 'p.id', '=', 'rp.product_id')
            ->select('rp.*', 'pu.quantity AS user_stock', 'p.name', 'p.price', 'p.id')
            ->where('rp.request_id', $id)->get()->paginate($limit);
        //return Request::findorfail($id)->products()->paginate($limit);
    }

    public function getDetail(int $id): Request 
    {
        return Request::findorfail($id);
    }

    public function requestSent(int $id)
    {
        $this->db->beginTransaction();
        $request = Request::findorfail($id);
        $request->status = '1';
        $request->save();
        $this->db->commit();
    }

    public function requestDone(int $id)
    {
        $this->db->beginTransaction();
        $request = Request::findorfail($id);
        $request->status = '2';
        $request->save();
        $products = $request->products;
        foreach ($products as $p) {
            if($p->type == 'stock_kg') {
                $inc = $p->pivot->quantity * 1000;
                DB::table('product_user')->where('user_id', $request->user_id)
                    ->where('product_id', $p->id)->increment('quantity', $inc);
            }
            else if($p->min_stock_unit != 'carton') {
                DB::table('product_user')->where('user_id', $request->user_id)
                    ->where('product_id', $p->id)->increment('quantity', $p->pivot->quantity);
            }
            else {
                $inc = $p->pivot->quantity * $p->per_stock;
                DB::table('product_user')->where('user_id', $request->user_id)
                    ->where('product_id', $p->id)->increment('quantity', $inc);
            }
        }
        $this->db->commit();
        return $request;
    }
}