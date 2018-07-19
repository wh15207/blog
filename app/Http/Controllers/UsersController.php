<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests;
use Auth;
use Psy\Exception\ErrorException;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth',[
           'except' => ['show','create','store','index']
        ]);
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    public function create(){
        return view('users/create');
    }
    public function show(User $user){
         return view('users.show',compact('user'));
    }
    public function store(Request $request){
        $this->validate($request,[
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        Auth::login($user);
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show',[$user]);
    }
    public function edit(User $user){
        try {
            $this->authorize ('update', $user);
            return view ('users.edit', compact ('user'));
        } catch (\Exception $e) {
            session()->flash('danger','您无权修改他人资料');
            return redirect()->route('home');
        }
    }
    public function update(User $user,Request $request){
        $this->validate($request,[
            'name' => 'required|max:50',
            'password' => 'required|confirmed|min:6'
        ]);

        $data = [];
        $data['name'] = $request->name;
        if($request->password){
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        session()->flash('success','个人资料更新成功!');

        return redirect()->route('users.show',$user->id);
    }
    public function index(){
        $users = User::orderBy('id','asc')->paginate(10);
        return view('users.index',compact('users'));
    }
    public function destroy(User $user)
    {
        try{
            $this->authorize('destroy', $user);
            $user->delete();
            session()->flash('success', '成功删除用户！');
            return back();
        }catch(\Exception $e){
            session()->flash('danger','您无权删除用户');
            return redirect()->route('home');
        }

    }
}
