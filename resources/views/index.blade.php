{{--listing page--}}
<x-layout title="Article Listings">
    {{--cover image place--}}
    <div class="cover-image">
    </div>
    <h1 class="title">Article Listings</h1>
    <div class="content index">
        <div class="main-area content-area">
            <em>Folders:</em>
            <ul>
                @php
                    //get current path
                    $path = url()->current();
                    if(!str_contains($path, 'heritages')){
                        $path = $path .'/heritages';
                    }
                 @endphp
                {{--render out folders first--}}
                @foreach($folders as $folder)
                    <li>
                        <a href="{{$path . '/'.$folder }}">{{$folder}}</a>
                    </li>
                @endforeach
            </ul>
            <em>Articles:</em>
            <ul>
                {{--render out files--}}
                @foreach($files as $file)
                    <li>
                        <a href="{{$path . '/'.$file['filename']}}">{{$file['title']}}</a>
                        <a class="summary" href="{{$path . '/'.$file['filename']}}">{{$file['summary']}}</a>

                    </li>
                @endforeach
            </ul>
        </div>
        {{--article search area--}}
        <aside class="content-area search-area">
            <h2>Search</h2>
            {{--search form--}}
            <div class="form-wrapper">
                <form action="{{route('view.search')}}" method="GET">
                    <div class="input-wrapper">
                        <label for="keyword">Keywords:</label>
                        <input type="text" name="keyword" id="keyword" required>
                    </div>
                    <input type="submit" value="Search">
                </form>
            </div>
            <a href="{{route('view.index')}}">Back to Main Listing</a>
        </aside>
    </div>
</x-layout>
