@extends('master')
@section('content')
<style>
    .template-container {
        margin: 10px;
        text-align: center;
        border: 1px solid #ccc;
        padding: 10px;
        border-radius: 5px;
        width: 300px;
    }
    .template-container img {
        width: 100%;
        height: auto;
    }
    .template-wrapper {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }
</style>

<h2 class="display-5 fw-bolder text-center"><span class="text-gradient d-inline">Select Template</span></h2>
<div class="template-wrapper">
    @foreach($templates as $template)
        <div class="template-container">
            <img src="{{ asset('storage/' . $template->image_path) }}" alt="{{ $template->name }}">
            <p>{{ $template->name }}</p>
            <button class="btn btn-primary btn-sm px-3 py-2 me-sm-2 fs-6 fw-bolder" onclick="chooseTemplate('{{ $template->id }}')">Select</button>
        </div>
    @endforeach
</div>
<script>
    function chooseTemplate(templateId) {
        window.location.href = '/design/' + templateId;
    }
</script>
@endsection