
@extends('layout.createLayout')

@section('name',  __('messages.Edit_Budget') )
@section('route', route('budget.update', ['budget' => $budget->id]))

@section('form')
    @include('budget.form')
@stop
