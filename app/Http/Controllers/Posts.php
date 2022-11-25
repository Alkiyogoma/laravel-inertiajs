<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Player;
use App\Models\Gallery;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\{UserUpdateRequest,UserAddRequest};
use Spatie\Permission\Models\Role;
use App;
use Auth;
use Image;

class Posts extends Home_Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    { 
        //$this->authorize(User::class, 'index');
       
        $this->data['posts'] = Post::get();
        return view('posts.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {   
        if($_POST){
        $data = request()->except('attach');
       // dd($data);
        $filename = '';
        if (!empty(request('attach'))) {
            $file = request()->file('attach');
            $filename = time() . rand(10, 666) . '.' . $file->guessExtension();
            $filePath = base_path() . '/storage/uploads/images/';
            $file->move($filePath, $filename);
        }
        Post::create(array_merge($data, ['attach' => $filename, 'user_id' => Auth::user()->id]));
        return redirect('posts/index')->with('success', 'New Post created successfully');
    }
    $this->data['categories'] = Category::orderBy('id', 'DESC')->get();
    return view('posts.create', $this->data);
    }

    

    public function gallery()
    { 
       
        if($_POST){
            $data = request()->except('attach');
            $filename = '';
            if (!empty(request('attach'))) {
                $file = request()->file('attach');
                $fn = 'gallery'.time() . rand(10, 666) . '.';
                $filename = $fn. $file->guessExtension();
               
                $image = Image::make(request()->file('attach'));
                $image->orientate();
                $image->resize(560, 430)->save('storage/uploads/images/' . $filename);
            
            $gallery =   \App\Models\Gallery::create(array_merge($data, ['attach' => $filename, 'user_id' => Auth::user()->id]));
            $filename1 = '1'.$fn. $file->guessExtension();
            $image->save('storage/uploads/images/' . $filename1);
    
            }
            return redirect('posts/gallery')->with('success', 'New Post created successfully');
        }
        $this->data['categories'] = Category::orderBy('id', 'DESC')->get();
        $this->data['posts'] = \App\Models\Gallery::latest()->get();
        return view('posts.gallery', $this->data);
    }


    public function deleteGal()
    {
        $id = request()->segment(3);
        $file = Gallery::where('id', $id)->first();
        $filepath = 'storage/uploads/images/'.$file->attach;
        if (is_file($filepath))
            {
                unlink($filepath);
            }
            $this->data['post'] = Gallery::where('id', $id)->delete();
            return redirect('posts/gallery')->with('primary', 'New Post created successfully');

    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(UserAddRequest $request)
    {
        $user = User::create($request->all());
        $role = Role::find($request->role);
        if($role)
        {
            $user->assignRole($role);
        }
        return response()->json($user);
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $id = request()->segment(3);
    $this->data['post'] = Post::where('id', $id)->first();
    $this->data['next'] = Post::where('id', '<>', $id)->first()->id;

    return view('posts.view', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    { 
        
        return view('admin.users.edit', compact('user'));
    }

    public function saveComment()
    { 
       if($_POST){
          $post = \App\Models\PostComment::create(request()->all());
          $comment ='';
          $comment .= '<li class="media">
            <img alt="image" class="mr-3 rounded-circle" width="70" src="'.url('public/assets/img/avatar/avatar-3.png').'">
            <div class="media-body">
                <div class="media-right"><div class="text-warning">Pending</div></div>
                <div class="media-title mb-1">'.$post->user->name.'</div>
                <div class="text-time">'.timeAgo($post->created_at).'</div>
                <div class="media-description text-muted">'.$post->content.'
                </div>
                <div class="media-links">
                <a href="#">View</a>
                <div class="bullet"></div>
                <a href="#">Edit</a>
                <div class="bullet"></div>
                <a href="#" class="text-danger">Trash</a>
                </div>
            </div>
            </li>';
         
        echo $comment;
        return redirect()->back()->with('success', 'Congraturation Your Comment Received..' );

       }
    }


        public function player() {       
        
            if($_POST){
                $post = \App\Models\Player::create(request()->all());
                return redirect()->back()->with('success', 'Congraturation Your Player Submitted..' );
            }
            $this->data['players'] = Player::get();
            return view('posts.players', $this->data);
        }

        public function viewPlayer()
        {
            $id = request()->segment(3);
            if($_POST){
                $post = \App\Models\PlayerComment::create(request()->all());
                $comment ='';
                $comment .= '<li class="media">
                  <img alt="image" class="mr-3 rounded-circle" width="70" src="'.url('public/assets/img/avatar/avatar-3.png').'">
                  <div class="media-body">
                      <div class="media-right"><div class="text-warning">Pending</div></div>
                      <div class="media-title mb-1">'.$post->user->name.'</div>
                      <div class="text-time">'.timeAgo($post->created_at).'</div>
                      <div class="media-description text-muted">'.$post->content.'
                      </div>
                      <div class="media-links">
                      <a href="#">View</a>
                      <div class="bullet"></div>
                      <a href="#">Edit</a>
                      <div class="bullet"></div>
                      <a href="#" class="text-danger">Trash</a>
                      </div>
                  </div>
                  </li>';
              echo $comment;
              return redirect()->back()->with('success', 'Congraturation Your Comment Received..' );
      
             }
             $this->data['player'] = Player::where('id', $id)->first();
             $this->data['next'] = Player::where('id', '<>', $id)->where('status', 0)->first();
             return view('posts.view_player', $this->data);
        }
        
        public function singleSetting()
        {
            return view('setting.single-setting');
        }

        public function updates()
        {
            return $this->index();
        }

        public function tables()
        {
            return view('posts.dataTable');
        }

        public function comments()
        {
            $id = request()->segment(3);
            if((int)$id > 0){
                $this->data['comment'] = $comment = Comment::where('id', $id)->first();
            if($_POST){
                send_sms(str_replace('+', '', validate_phone_number(trim($comment->phone))[1]), request('about'));
                Comment::where('id', $id)->update(['status' => 1, 'reply' => request('about')]);
                return redirect('posts/comments')->with('success', 'Congraturation Your Reply Sent to '. $comment->name );
            }
            return view('posts.reply_comment', $this->data);
            }
            $this->data['comments'] = Comment::orderBy('status', 'ASC')->get();
            return view('posts.comments', $this->data);
        }
    
}
