<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{
            $posts = Post::get();
            $res = [
                'status'=>'success',
                'message' => 'Post list',
                'posts' => $posts
            ];
            return response($res,200);
        }catch(Exception $e){
            return response(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            $rules = [
                'title' => 'required|string|max:255',
                'body' => 'required|string',
            ];
            $messages = [
                'title.string' => 'Enter a valid title',
                'body.string' => 'Enter a valid body',
            ];
            $this->validate($request,$rules,$messages);

            $post = Post::create([
                'title' => $request->title,
                'body' => $request->body,
            ]);

            $res = [
                'status'=>'success',
                'message' => 'Successfully created the post',
                'post' => $post
            ];
            return response($res,201);
        }catch(Exception $e){
            return response(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try{
            $post = Post::find($id);
            if(!$post){
                return response(['status'=>'error','message'=>'Post not found'],404);
            }
            $res = [
                'status'=>'success',
                'message' => 'Post data',
                'post' => $post
            ];
            return response($res,200);
        }catch(Exception $e){
            return response(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try{
            $post = Post::find($id);
            if(!$post){
                return response(['status'=>'error','message'=>'Post not found'],404);
            }
            
            $rules = [
                'title' => 'required|string|max:255',
                'body' => 'required|string',
            ];
            $messages = [
                'title.string' => 'Enter a valid title',
                'body.string' => 'Enter a valid body',
            ];
            $this->validate($request,$rules,$messages);

            $post->update([
                'title' => $request->title,
                'body' => $request->body,
            ]);

            $res = [
                'status'=>'success',
                'message' => 'Successfully updated the post',
                'post' => $post
            ];
            return response($res,201);
        }catch(Exception $e){
            return response(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $post = Post::find($id);
            if(!$post){
                return response(['status'=>'error','message'=>'Post not found'],404);
            }

            $post->delete();
            return response(['status'=>'success','message'=>'Successfully deleted the post'],200);
        }catch(Exception $e){
            return response(['status'=>'error','message'=>$e->getMessage()],500);
        }
    }

}
