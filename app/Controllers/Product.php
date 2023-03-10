<?php

namespace App\Controllers;

use Config\Database;
use App\Models\ProductModel;
use InvalidArgumentException;
use App\Controllers\BaseController;

class Product extends BaseController
{
    protected $products;
    protected $db;

    public function __construct()
    {
        $this->products = new ProductModel();
        $this->db = Database::connect();
    }
    
    public function index()
    {
        $products = $this->products->findAll();

        return $this->response->setJSON([
            'meta' => [
                'message' => 'List Products',
                'code' => 200,
            ],
            'data' => $products,
        ]);
    }

    public function show($productId)
    {
        $product = $this->products->find($productId);
        
        if (! $product) {
            return $this->response->setJSON([
                'meta' => [
                    'message' => 'Products Not Found',
                    'code' => 404,
                ],
                'data' => null,
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'meta' => [
                'message' => 'Prodcut Detail',
                'code' => 200,
            ],
            'data' => $product,
        ]);
    }

    public function create()
    {
        $this->db->transBegin();

        try {
            // data request product
            $data = [
                'name' => $this->request->getPost('name'),
            ];

            // Save data while check validation
            if ($this->products->save($data) === false) {
                $errorMessages = [];
                foreach ($this->products->errors() as $error) {
                    $errorMessages[] = $error;
                }
                throw new InvalidArgumentException(json_encode($errorMessages), 422);
            }
            
            $this->db->transCommit();

        } catch (InvalidArgumentException $e) {

            $this->db->transRollback();

            $errorMessages = json_decode($e->getMessage());
            if ($errorMessages == null) {
                $validationFailed = $e->getMessage();
            } else {
                $validationFailed = 'Validation Fail';
            }

            return $this->response->setJSON([
                'meta' => [
                    'message' => $validationFailed,
                    'code' => $e->getCode(),
                ],
                'errors' => $errorMessages ?? null,
            ])->setStatusCode($e->getCode());
        }

        return $this->response->setJSON([
            'meta' => [
                'message' => 'Success Create Product',
                'code' => 200,
            ],
            'data' => null,
        ]);
    }

    public function update($productId)
    {
        $this->db->transBegin();

        try {
            // data request product
            $data = [
                'id' => $productId,
                'name' => $this->request->getPost('name'),
            ];

            $product = $this->products->find($productId);

            if (! $product) {
                throw new InvalidArgumentException('Product Not Found', 404);
            }


            // Save data while check validation
            if ($this->products->update($productId,$data) === false) {
                $errorMessages = [];
                foreach ($this->products->errors() as $error) {
                    $errorMessages[] = $error;
                }
                throw new InvalidArgumentException(json_encode($errorMessages), 422);
            }
            
            $this->db->transCommit();

        } catch (InvalidArgumentException $e) {

            $this->db->transRollback();

            $errorMessages = json_decode($e->getMessage());
            if ($errorMessages == null) {
                $validationFailed = $e->getMessage();
            } else {
                $validationFailed = 'Validation Fail';
            }

            return $this->response->setJSON([
                'meta' => [
                    'message' => $validationFailed,
                    'code' => $e->getCode(),
                ],
                'errors' => $errorMessages ?? null,
            ])->setStatusCode($e->getCode());
        }

        return $this->response->setJSON([
            'meta' => [
                'message' => 'Success Update Product',
                'code' => 200,
            ],
            'data' => null,
        ]);
    }

    public function delete($productId)
    {
        $this->db->transBegin();
        try {
            $product = $this->products->find($productId);
    
            if (! $product) {
                throw new InvalidArgumentException('Product Not Found', 404);
            }

            $this->products->delete($productId);

            $this->db->transCommit();

        } catch (InvalidArgumentException $e) {
            $this->db->transRollback();
            return $this->response->setJSON([
                'meta' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                ],
                'errors' => null,
            ])->setStatusCode($e->getCode());
        }

        return $this->response->setJSON([
            'meta' => [
                'message' => 'Success Delete Product',
                'code' => 200,
            ],
            'data' => null,
        ]);
    }
}
