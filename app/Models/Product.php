<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'price',
        'discountPercentage',
        'stock',
        'brand',
        'categoryId',
        'thumbnail',
        'images',
        'sellerId',
    ];

    public function saveProductThumbnail($thumbnail)
    {
        $thumbnailName = $thumbnail->getClientOriginalName();
        $thumbnailPath = $thumbnail->storeAs("Product/Product_$this->id/thumbnail", $thumbnailName, 'public');
        $thumbnailUrl =  url("uploads/$thumbnailPath");
        return $thumbnailUrl;
    }

    public function saveProductImages($uploadedImages)
    {
        $images = [];
        foreach ($uploadedImages as $image) {
            $imageName = $image->getClientOriginalName();
            $imagePath = $image->storeAs("Product/Product_$this->id", $imageName, 'public');
            $images[] = url("uploads/$imagePath");
        }
        return $images;
    }

    public function deleteProductThumbnail()
    {
        $baseFileName = basename($this->thumbnail);
        Storage::disk('public')->delete("Product/Product_$this->id/thumbnail/{$baseFileName}");
        File::deleteDirectory("uploads/Product/Product_$this->id/thumbnail");
    }

    public function deleteProductImages()
    {
        $allImages = explode(',', $this->images);
        foreach ($allImages as $key => $image) {
            $imageName = basename($image);
            Storage::disk('public')->delete("Product/Product_$this->id/{$imageName}");
        }
        if (!File::isDirectory("uploads/Product/Product_$this->id/thumbnail")) {
            File::deleteDirectory("uploads/Product/Product_$this->id");
        }
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reduceStock($quantity)
    {
        if ($this->stock >= $quantity) {
            $this->stock -= $quantity;
            $this->save();
            return true;
        }
        return false; // Not enough stock
    }

}
