<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Item;
use App\Restaurant;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {   
        $restaurant = Restaurant::findOrFail($id);
        $items = $restaurant->items;
        return view('admin.items.index', compact('items', 'restaurant'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
         $restaurant = Restaurant::findOrFail($id);
        return view('admin.items.create', compact('restaurant'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $id)
    {
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
        $newItem->available = $data['available'] ? true : false;
        $newItem->restaurant_id = $restaurant->id;

        $newItem->save();

        return redirect()->route('admin.items.index', $restaurant->id);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($restaurant_id, $item_id)
    {
        $item = Item::findOrFail($item_id);

        $restaurant = Restaurant::findOrFail($restaurant_id);

        return view('admin.items.edit', compact('item', 'restaurant'));
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
 
        $data = $request->all();

        $items = Item::all();
        $item = Item::findOrFail($item_id);

        // dd('dioboia di piatto : '.$item);

        $restaurant = Restaurant::findOrFail($item['restaurant_id']);

        dump($restaurant);

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

        $item->update();


        

        return redirect()->route('admin.items.index', $restaurant->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
