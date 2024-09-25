{{--listing and article layout--}}
    <!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{url("style/app.css?v=".\Illuminate\Support\Carbon::now()->timestamp)}}">
    <title>{{$title}}</title>
    <meta name="author" content="Mark Kiss" />
    <meta name="description" content="Discover what Lyon can offer you through articles." />
</head>
<body>
<div class="wrapper">
    {{$slot}}
</div>


</body>
</html>
