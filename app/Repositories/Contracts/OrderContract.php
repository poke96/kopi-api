<?php
/**
 * Created by PhpStorm.
 * User: Daniel
 * Date: 7/11/2017
 * Time: 5:37 PM
 */

namespace App\Repositories\Contracts;


use App\Models\Order;

interface OrderContract
{
    public function create(int $userId, array $products): Order;
}