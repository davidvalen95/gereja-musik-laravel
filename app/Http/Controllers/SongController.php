<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;
use App\Helper\Form;
use App\Model\Song;
use App\Model\SongDetail;
use Session;
class SongController extends Controller
{
    private $user;
    private $message;
    public function __construct(){
        $this->middleware(function($request,$next){
            // debug(Auth::id());
            $this->user = User::find(Auth::id());
            $this->user->setDefaultPreferences();
            $this->message = (Session::get('message'));
            return $next($request);
        });

        $this->middleware('auth');
    }



    public function index(){
        $data['title'] = 'Home | '.TITLE;
        $data['user'] = $this->user;



        // Session::forget('message');
        return view('',$data);
    }

    public function getNewSong(){
        $data['title'] = 'Home | '.TITLE;
        $data['user'] = $this->user;

        //placeholder, name, type, icon, options:array
        $title = new Form('Song Title','title','text',"");
        // $verse = new Form('Verse(bait)','bait','textarea',"");
        $lyric = new Form('Lyric','lyric','editor',"");
        $foto = new Form('Url Image Cover (ex. https://pbs.twimg.com/profile_images/710468502457442304/f-8UB2T1.jpg)', 'imageUrl', 'text', "");
        $data['forms'] = array($title, $foto, $lyric);

        // Session::forget('message');
        return view('song.newSong',$data);
    }

    public function postNewSong(Request $request){
        $request->flash();
        $this->validate($request,array(
            'title' => "required|unique:song",
            'lyric' => 'required'

        ));



        $song = new Song($request->all());
        $song->user_id = $this->user->id;
        $song->save();


        $urlSong = getUrlFormat($song->title);
        $request->session()->flash('message.success', "New song created");
        return redirect("song/$urlSong/$song->id");
    }

    public function getSongDetail($title, $id, Request $request){
        $song = Song::find($id);
        if(!$song){
            return redirect('/');
        }
        $song->setDefaultPreferences();
        $data['title']  = "$song->title | ". TITLE;
        $data['user']   = $this->user;
        $data['song']   = $song;
        $data['songDetails'] = $song->getSongDetail;;

        //placeholder, name, type, icon, options:array
        $titleForm          = new Form("Video title", "title", "text", "");
        $descriptionForm    = new Form("Video description", "description", "text", "");
        $urlForm            = new Form("Video embed url", "embedUrl", "text","");
        $songId             = new Form("song id", "song_id", "hidden", "", [], "$song->id");
        $data['forms'] =  array($titleForm, $descriptionForm,$urlForm,$songId);



        // // Session::forget('message');
        return view('song.songDetail',$data);

    }

    public function postSongDetail(Request $request){


        $this->validate($request, array(
            'title' => 'required',
            'description' => 'required',
            'embedUrl'  => 'required|regex:(^[^\/]+$)'

        ));


        $songDetail = new SongDetail($request->all());

        $songDetail->user_id = Auth::id();
        // debug(Auth::id());
        $songDetail->save();


        $request->session()->flash('message.success', "Video arangement submited!");

        return redirect()->back();
    }
}