<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="">
    <!-- <link rel="shortcut icon" type="image/png/ico" href="/public_paths/favicon.ico" /> -->
    @if($data && $data[0])
    <title>{{$data[0]->event_details->title}}</title>
    <meta property="fb:app_id" content="2920021121630379" />
    <meta property="type" content="website" />
    <meta property="url" content="https://bfss-api.5ppsite.com/event/{{$data[0]->id}}" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://bfss-api.5ppsite.comevent/{{$data[0]->id}}" />
    <meta property="og:title" content="{{$data[0]->title}}" />
    <meta property="og:image" content="{{asset('storage/'.$data[0]->upload)}}" />
    <meta property="og:description"
        content="{{date('d M, Y', strtotime($data[0]->date_from))}} - {{date('d M, Y', strtotime($data[0]->date_to))}}, {{"\n".$data[0]->event_details->descrition}}" />

    <meta name="msapplication-TileColor" content="#ffffff" />
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png" />
    <meta name="theme-color" content="#ffffff" />
    <meta name="_token" content="" />
    <meta name="robots" content="noodp" />
    <meta property="title" content="{{$data[0]->event_details->title}}" />
    <meta property="quote"
        content="{{date('d M, Y', strtotime($data[0]->date_from))}} - {{date('d M, Y', strtotime($data[0]->date_to))}}, {{"\n".$data[0]->event_details->descrition}}" />
    <meta name="description"
        content="{{date('d M, Y', strtotime($data[0]->date_from))}} - {{date('d M, Y', strtotime($data[0]->date_to))}}, {{"\n".$data[0]->event_details->descrition}}" />
    <meta property="image"
        content="{{asset('storage/'.($data[0]->event_details ? $data[0]->event_details->upload : ''))}}" />
    <meta property="og:locale" content="en_US" />
    <meta property="og:hashtag" content="#event" />
    <meta content="image/*" property="og:image:type" />
    <meta property="og:site_name" content="CELIYA" />
    @endif
</head>

<body>
    <!-- <pre>

        < ?= $data[0]->date_from ?>
        < ?php print_r($data ? $data[0] : []); ?>

    </pre> -->

    <script>
    window.location.replace('https://myceliya2022.5ppsite.com/event/{{$data[0]->id}}')
    </script>
</body>

</html>