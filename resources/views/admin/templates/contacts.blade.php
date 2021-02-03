@extends("admin.templates.admin")
<?php

use Illuminate\Support\Facades\DB;

$parentPages = DB::table("sp_adminpages")->where("parentId", 5)->get();
?>
@section("content")
<ul class="productMenu">
    @if(Request::session()->get("activeUser")->roleId==1)
    @foreach($parentPages as $parentPage)
    <li><a href="{{URL::to('/')}}/{{$parentPage->pageURL}}">{{$parentPage->pageName}}</a></li>
    @endforeach
    @endif
    @if(Request::session()->get("activeUser")->roleId==0 )
    @foreach($parentPages as $parentPage)
    <?php
    $userPages = DB::table("sp_userpages")->where("pageId", $parentPage->pageId)->where("userId", session()->get("activeUser")->uId)->get();
    if (count($userPages) >= 1) {
    ?>
        <li>
            <a href="{{URL::to('/')}}/{{$parentPage->pageURL}}">
                <span><span>{{$parentPage->pageName}}</span></span></a>
        </li>
    <?php
    }
    ?>
    @endforeach
    @endif
    <!-- <li ng-repeat="page in pages"><a href="{{URL::to('/')}}/@{{page.pageURL}}" ng-if="page.parentId==3 && page.isActive==1">@{{page.pageName}}</a></li> -->
    <!-- <li><a href="{{URL::to('admin/contacts/head')}}">Contacts Head</a></li>
    <li><a href="{{URL::to('admin/contacts/subscribers')}}">Subscribed Email</a></li>
    <li><a href="{{URL::to('admin/contacts/new')}}">Add Contacts</a></li>
        <li><a href="{{URL::to('admin/contacts/designations')}}">Designation</a></li>
    <li><a href="{{URL::to('admin/contacts/services')}}">Products/Services</a></li>
    <li><a href="{{URL::to('admin/contacts/countries')}}">Countries</a></li>
    <li><a href="{{URL::to('admin/contacts/find')}}">Find</a></li> -->
</ul>
<style>
    ul.productMenu {
        list-style-type: none;
        padding-left: 30px;
        width: 100%;
        background-color: #ccc;
        font-size: 12px;
        font-weight: bolder;
        position: fixed;
        z-index: 100;

    }

    ul.productMenu li {
        display: inline-block;
    }

    ul.productMenu li a {
        display: inline-block;
        padding: 0px 10px;
        color: black;
        border-right: 1px solid gray;
    }
</style>
<br />
<div class="container-fluid">
    @yield("contacts_content")
</div>
@endsection
