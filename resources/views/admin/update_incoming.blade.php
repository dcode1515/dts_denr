@extends('layout.template')
@section('content')
  <!-- Mini Stat Cards – now with Font Awesome icons -->
    <div id = "app">
            <Updateincoming :incoming="{{ json_encode($incoming) }}"></Updateincoming>

    </div>
@endsection