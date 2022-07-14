<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Item;
use App\Tag;
use App\Restaurant;
use App\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    protected $validationRules=[
        "name" => "required|string|max:150",
        "price" => "required|numeric|between:0.20,999.99",
        "image" => "nullable|image|max:2048",
        "description" => "required|string|max:1000"
    ];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {   
        $tags = Tag::all();
        $categories = Category::all();
        $restaurant = Restaurant::findOrFail($id);
        $items = $restaurant->items;
        return view('admin.items.index', compact('items', 'restaurant', 'tags', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        $categories = Category::all();
        $tags = Tag::all();
        $restaurant = Restaurant::findOrFail($id);
        return view('admin.items.create', compact('restaurant', 'tags', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {
        $request->validate($this->validationRules);
        $data = $request->all();

        $restaurant = Restaurant::findOrFail($id);

        $newItem = new Item();

        $newItem->name = $data['name'];

        $slug = Str::of($data['name'])->slug("-");
        $count = 1;
        while(Item::where('slug', $slug)->first()){
            $slug = Str::of($data['name'])->slug("-")."-{$count}";
            $count++;
        }
        $newItem->slug = $slug;

        $newItem->price = $data['price'];
        if( isset($data['image']) ) {
            $path_image = Storage::put("uploads/items", $data['image']); // uploads/nomeimg.jpg
            $newItem->image = $path_image;
        }
        $newItem->description = $data['description'];
        $newItem->available = isset($data['available']);
        $newItem->restaurant_id = $restaurant->id;
        $newItem->category_id = $data['category'];
        $newItem->save();

        if (isset($data['tags'])) {
        $newItem->tags()->sync($data['tags']);
        }

        return redirect()->route('admin.items.show', [ 'id' => $restaurant->id, 'item' => $newItem->id ]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($rest_id, $item_id)
    {
        $item = Item::findOrFail($item_id);
        return view('admin.items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($restaurant_id, $item_id)
    {
        $tags = Tag::all();
        $categories = Category::all();
        $item = Item::findOrFail($item_id);

        $restaurant = Restaurant::findOrFail($restaurant_id);

        return view('admin.items.edit', compact('item', 'restaurant', 'tags', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $rest_id, $item_id)
    {
 
        $request->validate($this->validationRules);
        $data = $request->all();

        $item = Item::findOrFail($item_id);

        $restaurant = Restaurant::findOrFail($item['restaurant_id']);

        if ($item->name != $data['name']){
            $item->name = $data['name'];

            $slug = Str::of($data['name'])->slug("-");
            $count = 1;
            while(Item::where('slug', $slug)->first()){
                $slug = Str::of($data['name'])->slug("-")."-{$count}";
                $count++;
            }
            $item->slug = $slug;
        }


        $item->price = $data['price'];
        if( isset($data['image']) ) {
            $path_image = Storage::put("uploads/items", $data['image']); // uploads/nomeimg.jpg
            $item->image = $path_image;
        }
        $item->description = $data['description'];
        $item->available = isset($data['available']);
        $item->restaurant_id = $restaurant->id;
        $item->category_id = $data['category'];
        $item->update();

        if (isset($data['tags'])){
            $item->tags()->sync($data['tags']);
        }

        return redirect()->route('admin.items.show', [ 'id' => $restaurant->id, 'item' => $item->id ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($restaurant_id, $item_id)
    {
        $item = Item::findOrFail($item_id);
        $item->tags()->sync([]);
        $item->delete();
        return redirect()->route('admin.items.index', $restaurant_id);
    }
}
