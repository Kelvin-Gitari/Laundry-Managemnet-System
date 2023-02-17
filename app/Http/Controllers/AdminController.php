<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = User::query();

        if ($request->filled('search')) {
            $data->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orWhere('career', 'like', '%' . $request->search . '%');
        }

        $data = $data->paginate(10);

        return view('users.index', compact(
            'data'
        ));

        //sino soft added this 

        $data = User::orderBy('id','DESC')->paginate(5);
        return view('users.index',compact('data'))
            ->with('i', ($request->input('page', 1) - 1) * 5);

            //sino soft added this END
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        // boacked up from previous below is sino soft new ->return view('users.create');


        $roles = Role::pluck('name','name')->all();
        return view('users.create');
          
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255|unique:users',
            'password'        => 'required|string|min:6|same:password_confirmation',
         
        ]);

        $request->request->add([
            'password' => Hash::make($request->password),
        ]);

    
        if (!$request->filled('is_mploy')) {
            $request->request->add([
                'is_mploy' => false,
            ]);
        } else {
            $request->request->add([
                'is_mploy' => true,
            ]);
        }

        $user = User::create($request->all());

        return redirect()->route('users.index')
            ->with('success', __('Created successfully'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {

        return view('users.edit', compact(
            'user'
        ));
       
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            
            'email'           => 'required|email|unique:users,email,' . $user->id,
            'password'        => 'nullable|string|min:6|same:password_confirmation',
          
        ]);

        if ($request->filled('password')) {
            $request->request->add([
                'password' => Hash::make($request->password),
            ]);
        } else {
            $request->request->remove('password');
        }

        if (!$request->filled('is_admin')) {
            $request->request->add([
                'is_admin' => false,
            ]);
        }
        if (!$request->filled('is_mploy')) {
            $request->request->add([
                'is_mploy' => false,
            ]);
        }

       
        $user->update($request->all());

        return redirect()->route('users.edit', $user)
            ->with('success', __('Updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id == $user->id) {
            return redirect()->route('users.index')
                ->with('error', __('You can\'t remove yourself.'));
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', __('Deleted successfully'));
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        return view('auth.profile', compact(
            'user'));
    }

    public function profile_update(Request $request)
    {
        $request->validate([
            'name'     => 'required|max:255',
            'password' => 'same:password_confirmation',
        ]);

        if ($request->filled('password')) {
            $request->request->add([
                'password' => Hash::make($request->password),
            ]);
        } else {
            $request->request->remove('password');
        }

        $request->user()->update($request->all());

        return redirect()->route('profile.index')
            ->with('success', __('Updated successfully'));
    }
}
