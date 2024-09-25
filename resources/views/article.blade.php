{{--article file--}}
<x-layout title="{{$file['title']}}">
    <div class="cover-image article">
        <div class="placeholder"></div>
        <img src="{{url('images/'.$file['cover'])}}" alt="Cover image for {{$file['title']}}">
    </div>
    {{--title--}}
    <h1 class="title">{{$file['title']}}</h1>
    {{--content--}}
    <div class="content article">
        <div class="main-area content-area">
            {!! $file['content'] !!}
        </div>
        <aside class="content-area meta-info">
            <span>Date: {{$file['date']}}</span>
            <div class="tags">
                <span>Tags:</span>
                @foreach($file['tags'] as $tag)
                    <a href="{{route('view.search.tag', compact('tag'))}}">{{$tag}}</a>
                @endforeach
                @if(empty($file['tags']))
                    <em>No tags</em>
                @endif
            </div>
            <span>Draft:
                @if($file['draft'])
                    true
                @else
                    false
                @endif
            </span>
        </aside>
    </div>
    {{--placeholder for large photo--}}
    <div class="large-photo">
        <img src="" alt="">
    </div>
    {{--insert javascript--}}
    <script src="{{url('js/script.js')}}"></script>
    <script src="{{asset('js/script.js')}}"></script>
</x-layout>
