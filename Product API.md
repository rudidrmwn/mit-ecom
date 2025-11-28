```

**Cara Penggunaan API:**

1. **GET All Products (Index)**
```
   GET /api/products?page=1&per_page=10
```

2. **GET Single Product (Show)**
```
   GET /api/products/{id}
```

3. **POST Create Product (Store)**
```
   POST /api/products
   Content-Type: application/json
   
   {
       "name": "Product Name",
       "description": "Product description",
       "price": 100000,
       "category_id": 1,
       "stock": 50,
       "sku": "PRD-001",
       "image": "product.jpg",
       "status": "active",
       "attributes": [
           {"key": "color", "value": "red"},
           {"key": "size", "value": "L"}
       ]
   }
```

4. **PUT/PATCH Update Product (Update)**
```
   PUT /api/products/{id}
   Content-Type: application/json
   
   {
       "name": "Updated Product Name",
       "price": 150000
   }
```

5. **DELETE Product (Destroy)**
```
   DELETE /api/products/{id}