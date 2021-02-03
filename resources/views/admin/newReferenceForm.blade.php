@extends("admin.templates.news")
@section("news_content")
    <h3>Add New Reference</h3>
    @if(isset($message))

        <div class="alert alert-success">
            {{$message}}
        </div>

    @endif
    <form action="" method="post" enctype="multipart/form-data">
        <label for="customerName">
            Customer Name
            <input type="text" name="customerName" id="customerName" class="form-control">
        </label>
        <label for="DeliveryScope">
            Delivery Scope
            <input type="text" name="deliveryScope" id="DeliveryScope" class="form-control">
        </label>
        <label for="ContactPerson">
            Contact Person
            <input type="text" name="contactPerson" id="ContactPerson" class="form-control">
        </label>
        <label for="ProjectStatus">
            Project Status
            <input type="text" name="projectStatus" id="ProjectStatus" class="form-control">
        </label>
        <label for="ReferenceLetter">
            Reference Letter
            <input type="file" name="fileToUpload" id="referenceLetter">
        </label>
        <input type="hidden" name="_token" value="{{csrf_token()}}">
        <input type="submit" value="Register Reference" class="btn btn-primary btn-block">
    </form>
@endsection