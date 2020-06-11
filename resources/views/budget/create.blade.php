
@extends('layout.createLayout')

@section('name', __('messages.Create_Budget'))
@section('route', route('budget.store'))

@section('form')
    @include('budget.form')
@stop
