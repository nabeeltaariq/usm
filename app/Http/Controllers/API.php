<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Machine;
use App\UploadedMachine;
use App\Catagories;
use App\Service;
use App\ServiceDetail;
use App\ContactHead;
use App\contact;
use App\ContactTeam;
use App\Common;
use App\PartCatagory;
use App\PartsSubCatagory;
use App\Designation;
use App\Subscriber;
use App\SparePart;
use App\UploadedThumbs;
use App\News;
use App\SavedTemplate;
use App\Testimonial;
use App\Reference;
use App\Event;
use Illuminate\Support\Facades\DB;

class API extends Controller
{
    //api functions start
    public function welcome_page_data_api()
    {
         $featuredProducts = Product::where("is_feature",1)->get();
          return response()->json(["statistics" => Catagories::ListWithTotal(),"totalMachines" => Catagories::getAllMachinesCount(),"featuredProducts"=> $featuredProducts]);
         
    }
    
    
    public function DeliveryInfo(Request $request)
    {
        $message="";
        $user = DB::table("sp_userInformation")->where("email",$request->email)->first();

        $info = [
            "companyName" => $request->companyName,
            "personName" => $request->personName,
            "address" => $request->addressLine1 . ' ' . $request->addressLine2,
            "city" => $request->city,
            "postCode" => $request->postCode,
            "countryCode" => $request->country,
            "phoneNo" => $request->phoneNumber,
            "paymentMode"=>$request->method

        ];
        if(DB::table("sp_userInformation")->where("email",$request->email)->update($info))
        {
            
            $message="success";
           
        }
        else
        {
             $message="failure";  
        }
         return response()->json(["message"=>$message,"user"=>$user]);
    }
    public function AddSubscriber (Request $request)
    {
        $message = "";
        if(!empty($request->email)){
           $count = Subscriber::where("email_add",$request->email)->count();
           if($count <= 0){
                $newSubscriber = new Subscriber();
                $newSubscriber->title = "Subscriber";
                $newSubscriber->email_add = $request->email;
                $newSubscriber->country = "country";
                $newSubscriber->selected_language = "English";
                $newSubscriber->save();
                $message = "Your subscription recorded successfully";
           }else{
               $message = "You are already a subscriber";
           }
        }
        return response()->json(["message" => $message]);
    }
    
    public function AllMachinesCategories()
    {
        
        $allCatagories = Catagories::where('id',">=",1)->orderBy("id","asc")->get();
          $allProducts = Product::where('id',">=",1)->orderBy("id","desc")->get();
        return response()->json(["all-categories"=>$allCatagories,"all-machines"=>$allProducts]);
     
    }
    
    public function fetchAllMachinesCategories()
    {
        
        $allCatagories = Catagories::where('id',">=",1)->orderBy("id","asc")->get();
          $allProducts = Product::where('id',">=",1)->orderBy("id","desc")->get();
        return response()->json(["all-categories"=>$allCatagories,"all-machines"=>$allProducts]);
     
    }
    
     public function fetchFewMachines(Request $request)
    {
       
             $cat_id=(int)$request->input('cat_id');
              $allCatagories = Catagories::where('id',">=",1)->orderBy("order","asc")->get();
              $allProducts = Product::where('cat_id',$cat_id)->get();
             return response()->json(["all-categories"=>$allCatagories,"machines"=>$allProducts]);
    }
    
    
     public function DisplaySpareParts(){

        
         $allMachines = Machine::all();
          $allParts = SparePart::all();
      
         return response()->json(["machines" => $allMachines,"all-spare-parts"=>$allParts ]);
    }
    
    public function uploadYourMachine(Request $request)
    {
        
         $machine = new UploadedMachine();
        $machine->company = $request->input("company");
        $machine->personName = $request->input("name");
        $machine->phone = $request->input("phone");
        $machine->email = $request->input("email");
        $machine->technicalSpecifications = $request->input("technicalSpecifications");
        $machine->machineName = $request->input("machineName");
        $path = $request->file("featuredImage")->store("products");
        $common = new Common();
        $path = $common->SimplifiedPath($path);
        $machine->featuredImage = $path;
        $machine->isApproved = 0;
        if($machine->save())
        {
            return response()->json(["message"=>"Your machine is uploaded successfully"]);
            
             if($request->file("otherImages") != null)
             {
            foreach($request->file("otherImages") as $file){
            $path =  $file->store("products");
            $path = $common->SimplifiedPath($path);
                $thumb = new UploadedThumbs();
                $thumb->thumb_name = $path;
                $thumb->machine_id = $machine->id;
                $thumb->save();
                
            }
             }
            
        }
        else
        {
            return response()->json(["message"=>"Oops! Something went wrong"]);
        }
      

       
    }
    public function FetchNewsAndNewsletter(Request $request){
      
    
          $news = News::where("id",">=",1)->orderBy("id","desc")->get();
       
          $newsletter =  SavedTemplate::all();
          $testimonial = Testimonial::where("testimonialId",">=",1)->orderBy("testimonialId","desc")->get();
          $references = Reference::where("referenceId",">=",1)->get();
           $events = Event::where("eventId",">=",1)->orderBy("eventId","desc")->get();
        
        return response()->json(["news"=>$news,"newsletter"=>$newsletter,"testimonial"=> $testimonial,"references"=> $references,"events"=>$events]);
    }
    
    public function ProcessLogin(Request $request){
   $message = "";
    $user = DB::table("sp_userInformation")->where("email",$request->email)->where("password",base64_encode($request->password))->first();
    if($user != null){
       $message = "success";
    }else{

        $message = "failure";
    }

    return response()->json(['message'=>$message]);

}
   public function CreateProfile(Request $request){

  $isExist =  DB::table("sp_userInformation")->where("email",$request->email)->count();
  $message="";
    if($isExist){
        $message ="exists";
    }else if($request->password == $request->confirmpassword){
        


           
            if( DB::table("sp_userInformation")->insert(["email"=>$request->email,"password"=>base64_encode($request->password),"lock" => 1])
            )
          $message="success";

}
    else{
            $message="passwords do not match";
        }
    
    return response()->json(['message'=>$message]);

}
  //ContactUs Form
   public function QuoteFormContactUs(Request $request)
    {
        $to = "inquiry@trepak.pk";
        $subject = "Email from Machine page. Price Query.";
        $message = 'This Email is for Price Query - '. request('machine_name') .'  - Used Sweden Machines'. "\n";
        $message .='Machine Name: '.request('machine_name'). "\n";
        $message .='Item #:  '.  request('serial_no') . "\n";
        $message .='Name :  '.request('full_name')."\n";
        $message .='Phone #:  '. request('phone') ."\n";
        $message .='Email:  '.  request('email') ."\n";
        $message .='Company:  '. request('company')  ."\n";

        $message .='Special Request:  '.  request('request') ."\n" ;
        $message.="This email is fully backed up in database";

        $header = 'From:'.request('email');


   if(mail ($to,$subject,$message,$header))
       $flash_message="Email has been sent Successfully!";
        else
        $flash_message="Email has not been sent!";
        
     return response()->json(['message'=>$flash_message]);

    }
    
    // forget password
    public function ForgetPassword(Request $request)
    { 
        $userInfo =  DB::table("sp_userInformation")->where("email",$request->Email)->first();
  $message = "";

  if($userInfo){
    
   if( mail($request->Email,"Your USM Password","Welcome to USM\n Your forgotted password is: " . base64_decode($userInfo->password) . "\n","From:no-reply@usedswedenmachines.com"))
   $flash_message = "Email has been sent containing the  password of this email";
   else
   $flash_message = "Email has not been sent!";

}
else{
    $flash_message = "Sorry! Email does not exist in the System";
  }

  return response()->json(['message'=>$flash_message]);
}

//price details
   public function QuoteFormSubmit(){
        $to = "inquiry@trepak.pk";
        $subject = "Email from Machine page. Price Query.";
        $message = 'This Email is for Price Query - '. request('machine_name') .'  - Used Sweden Machines'. "\n";
        $message .='Machine Name: '.request('machine_name'). "\n";
        $message .='Item #:  '.  request('serial_no') . "\n";
        $message .='Name :  '.request('full_name')."\n";
        $message .='Phone #:  '. request('phone') ."\n";
        $message .='Email:  '.  request('email') ."\n";
        $message .='Company:  '. request('company')  ."\n";

        $message .='Special Request:  '.  request('request') ."\n" ;
        $message.="This email is fully backed up in database";

        $header = 'From:'.request('email');


	
  if(mail ($to,$subject,$message,$header))
 $flash_message="Email has been sent Successfully!";
        else
      $flash_message="Email has not been sent!";
      
   return response()->json(['message'=>$flash_message]);
   
    }
    
     //api functions start
    public function FetchMachines($id = null,Request $request){
        if($id == null){
            $request->session()->put("selectedCat","*");
            $request->session()->put("mode","all");
            return Product::where('id','>=',1)->orderBy("id","desc")->get();
        }else{
            $request->session()->put("mode","single");
            $request->session()->put("selectedCat",$id);
            return Product::where('cat_id','=',$id)->orderBy("id","desc")->get();
        }
    }

    public function ContactInfo($id,$type){
        $contactInfo = contact::where("contactTypeId",$type)->where("contactUdId",$id)->first();
        $contactTeamInfo = ContactTeam::where("contactId",$id)->where("contactTypeId",$type)->get();
        $buyProductRaw = DB::table("sp_products_rate")->where("customer_spplier_id",$contactInfo->contactId)->where("buyOrSell",1)->get();
        $buyProduct = [];
        foreach($buyProductRaw as $product){
             $pr = [];
             $pr["id"] = $product->productId;
             $pr["customer_spplier_id"] = $product->customer_spplier_id;
             $pr["rate"] = $product->rate;
             $pr["buyOrSell"] = $product->buyOrSell;
             $productInfo = DB::table("sp_productservice")->where("id",$product->productId)->first();
             $pr["name"] = $productInfo->name;
             array_push($buyProduct,$pr);
        }

        $sellProductRaw = DB::table("sp_products_rate")->where("customer_spplier_id",$contactInfo->contactId)->where("buyOrSell",2)->get();
        $sellProduct = [];
        foreach($sellProductRaw as $product){
             $pr = [];
             $pr["id"] = $product->productId;
             $pr["customer_spplier_id"] = $product->customer_spplier_id;
             $pr["rate"] = $product->rate;
             $pr["buyOrSell"] = $product->buyOrSell;
             $productInfo = DB::table("sp_productservice")->where("id",$product->productId)->first();
             $pr["name"] = $productInfo->name;
             array_push($sellProduct,$pr);
        }

         $response = [
             "response" => [
                 "info" => $contactInfo,
                 "team" => $contactTeamInfo,
                 "buyProducts" => $buyProduct,
                 "sellProducts" => $sellProduct
             ]
             ];
         return $response;
     }

    public function UpdateContactInfo(Request $request){
        $data = $request->All();
        $contact = contact::where("contactId",$data["contactId"])->first();
        $contact->companyName = $data['companyName'];
        $contact->contactUdId = $data['contactUdId'];
        $contact->contactTypeId = $data['contactTypeId'];
        $contact->registerAt = date("Y-m-d h:i:s");
        $contact->products = "";
        $contact->productToSell = "";
        $referenceCustomers = implode(",",$data["referenceCustomers"]);
        $contact->referenceCustomers = $referenceCustomers;
        $contact->portOfLoading = ($data["portOfLoading"] != null ? $data["portOfLoading"] : "");
        $contact->productService = $request->selectedService!==null?$request->selectedService:$contact->productService;
        $contact->address = $data["address"];
        $contact->country = $data["country"];
        $contact->city = $data["city"];
        $contact->postalCode = $data["postalCode"];
        $contact->currency = $data["currency"];
        $contact->telephone = $data["telephone"];
        $contact->fax = $data["fax"];
        $contact->email = $data["email"];
        $contact->web   = $data["web"];

        $contact->save();

        DB::table("sp_products_rate")->where("customer_spplier_id",$contact->contactId)->delete();
        foreach($data["buyProducts"] as $product){
            DB::table("sp_products_rate")->insert(["productId"=>$product["id"],"customer_spplier_id"=>$contact->contactId,"rate" => $product["rate"],"buyOrSell" => 1]);
        }

        foreach($data["sellProducts"] as $product){
            DB::table("sp_products_rate")->insert(["productId"=>$product["id"],"customer_spplier_id"=>$contact->contactId,"rate" => $product["rate"],"buyOrSell" => 2]);
        }


        DB::table("sp_contact_team")->where("contactId",$contact->contactUdId)->where("contactTypeId",$contact->contactTypeId)->delete();
        foreach($data["team"] as $member)
        {

            $team = new ContactTeam();
            $team->teamPersonName = (isset($member["teamPersonName"]) ? $member["teamPersonName"] : "");
            $team->designation = $member["selectedDesignation"]["name"];
            $team->mobileNo = (isset($member["mobileNo"]) ? $member["mobileNo"] : "");
            $team->email = (isset($member["email"]) ? $member["email"] : "");
            $team->skype = (isset($member["skype"]) ? $member["skype"] : "");
            $team->whatsapp = (isset($member["linkedIn"]) ? $member["linkedIn"] : "");
            $team->contactId = $data['contactUdId'];
            $team->contactTypeId = $contact->contactTypeId;
            $team->save();
        }
        return ["response" => "success"];
    }

    public function GetEmailsByType(Request $request){
       $contactTypeId = $request->contactType;
       $data = [];
       if($contactTypeId == -1){
           $allSubscribers = Subscriber::all();
           foreach($allSubscribers as $subscriber){
               array_push($data,["title" => $subscriber->email_add,"email" => $subscriber->email_add]);
           }
       }else if($contactTypeId != 6){
          $allContacts = contact::where("contactTypeId",$contactTypeId)->get();
          foreach($allContacts as $contact){
              if($contact->email != null && !empty($contact->email)){
                array_push($data,["title" => $contact->companyName,"email" => $contact->email]);
              }

              $teamMembers = ContactTeam::where("contactId",$contact->contactUdId)->where("contactTypeId",$contactTypeId)->get();
              foreach($teamMembers as $member){
                  if($member->email != null && !empty($member->email)){
                    array_push($data,["title" => $member->teamPersonName,"email" => $member->email]);
                  }
              }
          }
       }else if($contactTypeId == 6){
          $allEngineers =  DB::table("engineer_team")->get();
          foreach($allEngineers as $engineer){
            if($engineer->email != null && !empty($engineer->email)){
                array_push($data,["title" => $engineer->teamPersonName,"email" => $engineer->email]);
            }
          }
       }

       return $data;
    }

    public function FetchContactEmails($contactTypeId){
        $data = [];

        if($contactTypeId != 6){
            $allContacts = contact::where("contactTypeId",$contactTypeId)->get();
            foreach($allContacts as $c){
                    if(!empty($c->email)){
                    $email = [
                        "name" => $c->companyName,
                        "email" => $c->email
                    ];
                    array_push($data,$email);
                }
               $allTeam = ContactTeam::where("contactTypeId",$contactTypeId)->where('contactId',$c->contactUdId)->get();
               foreach($allTeam as $team){
                            if(!empty($team->email) && strlen($team->email) >= 2){
                                $email =[
                                "name" => $team->teamPersonName,
                                "email" => $team->email
                            ];
                            array_push($data,$email);
                    }
               }
            }
        }else{

        }

        return $data;
    }

  
    public function Fill(Request $request){

        $cartData = [];
        if($request->session()->has("cartData")){
            $cartData = $request->session()->get("cartData");
        }

        $item = [
            "partNo" => $request->input("partNo"),
            "partTitle" => $request->input("partTitle"),
            "price" => $request->input("price"),
            "status" => $request->input("status"),
            "manu" => $request->input("manu"),
            "quantity" => $request->input("quantity")
        ];



        array_push($cartData,$item);
        $request->session()->put("cartData",$cartData);
        return count($cartData);
      
      
    }

    public function UpateService(Request $request){
        $service = Service::find($request->input("id"));
        $service->name = $request->input("name");
       // $service->description = $request->input("description");
        $service->save();
        $serviceDetail = ServiceDetail::where("productId",$request->input("id"))->first();
        if($serviceDetail == null){
            $serviceDetail = new ServiceDetail();
            $serviceDetail->productId = $request->input("id");
        }
        $serviceDetail->height = $request->input("height");
        $serviceDetail->width = $request->input("width");
        $serviceDetail->qualityParameter = $request->input("qualityParameter");
        $serviceDetail->hsCode = $request->input("hsCode");
        $serviceDetail->valueAtCustom = $request->input("customWeight");
        $serviceDetail->weightInKg = $request->input("weightInKg");
        $serviceDetail->exchangeRate = $request->input("exchangeRate");
        $serviceDetail->customDuty = $request->input("customDuty");
        $serviceDetail->salesTax = $request->input("salesTax");
        $serviceDetail->rd = $request->input("rd");
        $serviceDetail->acd = $request->input("acd");
        $serviceDetail->ast = $request->input("ast");
        $serviceDetail->incomeTax = $request->input("incomeTax");
        $serviceDetail->sindhExcise = $request->input("sindhExcise");
        $serviceDetail->deliveryOrder = $request->input("deliveryOrder");
        $serviceDetail->portRent = $request->input("portRent");
        $serviceDetail->containerRent = $request->input("containerRent");
        $serviceDetail->insuranceCharges = $request->input("insuranceCharges");
        $serviceDetail->agencyCommission = $request->input("agencyCommission");
        $serviceDetail->roadTransportCharges = $request->input("roadTransportCharges");
        $serviceDetail->other = $request->input("other");
        $serviceDetail->save();
        return json_encode(["token" => "Successful"]);

    }

    public function FetchContactType(Request $request){
        return ContactHead::all();
    }

    public function ProcessContact(Request $request){
        $id = $request->input("id");

        $lastContact = contact::where("contactTypeId",$id)->orderBy("contactUdId","desc")->first();
        if($lastContact != null){
            $lastUDid =  $lastContact->contactUdId;
        }else{
            $lastUDid  = 0;
        }
        $lastUDid++;
        return json_encode(["newId" => $lastUDid]);
    }

    public function getservices(){
        return Service::all();
    }


    public function SaveContact(Request $request){
        $data = $request->All();
        $contact = new contact();
        $contact->companyName = $data['companyName'];
        $contact->contactUdId = $data['contactUdId'];
        $contact->contactTypeId = $data['type'];
        $contact->registerAt = date("Y-m-d h:i:s");
        $contact->products = "";
        $contact->productToSell = "";
        $referenceCustomers = implode(",",$data["referenceCustomers"]);


        $contact->referenceCustomers = $referenceCustomers;
        $contact->portOfLoading = ($data["portOfLoading"] != null ? $data["portOfLoading"] : "");
        $contact->productService = $data["selectedService"]["name"];
        $contact->address = $data["addressline1"];
        $contact->country = $data["country"];
        $contact->city = $data["city"];
        $contact->postalCode = $data["postalCode"];
        $contact->currency = $data["currency"];
        $contact->telephone = $data["telephone"];
        $contact->fax = $data["fax"];
        $contact->email = $data["email"];
        $contact->web   = $data["web"];

        $contact->save();

        foreach($data["buyProducts"] as $product){
            DB::table("sp_products_rate")->insert(["productId"=>$product["id"],"customer_spplier_id"=>$contact->contactId,"rate" => $product["rate"],"buyOrSell" => 1]);
        }

        foreach($data["sellProducts"] as $product){
            DB::table("sp_products_rate")->insert(["productId"=>$product["id"],"customer_spplier_id"=>$contact->contactId,"rate" => $product["rate"],"buyOrSell" => 2]);
        }


        foreach($data["team"] as $member)
        {
            $team = new ContactTeam();
            $team->teamPersonName = (isset($member["name"]) ? $member["name"] : "");
            $team->designation = $member["selectedDesignation"]["name"];
            $team->mobileNo = (isset($member["mobileNo"]) ? $member["mobileNo"] : "");
            $team->email = (isset($member["email"]) ? $member["email"] : "");
            $team->skype = (isset($member["skype"]) ? $member["skype"] : "");
            $team->whatsapp = (isset($member["linkedI"]) ? $member["linkedIn"] : "");
            $team->contactId = $data['contactUdId'];
            $team->contactTypeId = $data['type'];
            $team->save();
        }

        return $data;

    }


    public function UploadServicePicture(Request $request){
      $path =  $request->file("fileToUpload")->store("cms");
      $common = new Common();
      $path = $common->SimplifiedPath($path);
      $service = ServiceDetail::where("productId",$request->input("id"))->first();
      $service->pictureUrl = $path;
      $service->save();
      return json_encode(["path" => $path]);
    }

    public function PartsCategories($machineId){
        return PartCatagory::where("machine_id",$machineId)->get();
    }

    public function SubCategories($categoryId){
        return PartsSubCatagory::where("parent_id",$categoryId)->get();
    }

    public function getDesignations(){
        return Designation::all();
    }
}