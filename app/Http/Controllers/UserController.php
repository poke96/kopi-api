<?php

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use App\Transformer\ProductCategoryTransformer;
use App\Transformer\UserProductTransformer;
use Illuminate\Http\Request;

class UserController extends Controller
{

    private $repository;

    /**
     * ProductController constructor.
     */
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getproducts(Request $request)
    {
        $products = $this->repository->getProducts($request->user()->id);
        $result = $this->transformCollection($products, new UserProductTransformer());
        return $this->jsonReponse($result);
    }

    public function getCategories(Request $request)
    {
        $products = $this->repository->getCategoriedProduct($request->user()->id);
        $result = $this->transformCollection($products, new ProductCategoryTransformer());
        return $this->jsonReponse($result);
    }
}
