<?php 
require_once "../app/Mage.php";
Mage::app('default');
Mage::getSingleton("core/session",['name'=>'frontend']);
$client = new SoapClient('http://XXXXXXXXX.com/api/v2_soap/?wsdl');
// If somestuff requires api authentification,
// then get a session token
//your apiUser and apiKey create from backend
$session = $client->login('apiUser', 'apiKey');
$_userData=file_get_contents("php://input");
// I have developed this module for angular and ionic thats why need to a data decode process in json format
$request= json_decode($_userData, TRUE);
if($request['action'] == "create" ) {
		$request['website_id'] = 1;
		$request['store_id'] = 1;
		$request['group_id'] = 1;
	 try{
		 //echo 'hi';
		$result['data'] = $client->customerCustomerCreate($session, $request);
		$result['status'] = true;
		$result['message'] = 'Sign up successful.';
		$customerData = Mage::getModel('customer/customer')->load($result['data']);
		if($result['data']){
			$cust = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($customerData->getEmail());
          $cust->sendNewAccountEmail(
            'confirmation', 
            '', 
            Mage::app()->getStore()->getId()
        );

			echo json_encode($result);
		}
	
	}
	   catch( Exception $e ) {
		   $result['data'] = null;
		   $result['status'] = false;
		   $result['message'] = $e->getMessage();
		   echo json_encode($result);
	}
}
elseif ($request['action'] == "addtocartproductlist") {
 
 $customer_id= $request['customer_id'];
//$writeConnection->query($query);
$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
$sql        = "SELECT * FROM sales_flat_quote_item where cart_user_id='$customer_id'";
$rows       = $connection->fetchAll($sql); 
//$rows
//print_r($rows);
$cartlistp=array();
foreach($rows as $rowssku){
 //print_r($rowssku);
 //exit();
    $productsku= $rowssku['sku'];
   $quote_id= $rowssku['quote_id'];
   //$product_id= $rowssku['product_id'];
   $productqty= $rowssku['qty'];
   
if(!empty($productqty)){
 $anlproduct=Mage::getModel('catalog/product')->loadByAttribute('sku',$productsku);
 //$productlist = $anlproduct->getData();
 //print_r($anlproduct->getData());
 
 
 
  $stack = $anlproduct->getData();
 
  
  $stack['quote_id'] = $quote_id;
  //$stack2['product_id'] = $product_id;
  $stack['qty'] = (int)$productqty;
//$result['data'][]= $stack;
 $cartlistp[]= $stack;
 }
  }

		 try 
            {
				
                $result5 = $result;
				if(!empty($cartlistp)){
                        $result5['data'] = $cartlistp;
                        $result5['status'] = true;
                        $result5['message'] = 'Get Cart list of search data successfully.';	 
                        echo json_encode($result5);
              }else{
				  $result5['data'] = [];
                        $result5['status'] = true;
                        //$result5['message'] = 'Get Cart list of search data successfully.';	 
                        echo json_encode($result5);
			  }
	
            } 
            catch( Exception $e )
            {
          $result5['data'] = null;
		$result5['status'] = false;
		$result5['message'] = $e->getMessage();
		echo json_encode($result5);
            }				
						
}



if ($request['action'] == "passwordchange") {
    $customerid = $request['customerid'];
    $username = $request['username'];
    $oldpassword = $request['oldpassword'];
    $newpassword = $request['newpassword'];
    $storeid = '1';

    $websiteId = Mage::getModel('core/store')->load($storeid)->getWebsiteId();
    try {
        $login_customer_result = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->authenticate($username, $oldpassword);
        $validate = 1;
    } catch (Exception $ex) {
         $result1['data'] = [];
        $result1['status'] = false;
        $result1['message'] = $ex->getMessage();
        echo json_encode($result1);
    }
    if ($validate == 1) {
        try {
            $customer = Mage::getModel('customer/customer')->load($customerid);
            $customer->setPassword($newpassword);
            $customer->save();
            //$result = 'Your Password has been Changed Successfully';
            $result1['data'] = $customer->save();
            //$result['data'] = $test;
            //exit();
            $result1['status'] = true;
            $result1['message'] = 'Password changed successfully';
            echo json_encode($result1);
        } catch (Exception $e) {
            $result1['data'] = null;
            $result1['status'] = false;
            $result1['message'] = "Invalid current password";
            echo json_encode($result1);
        }
    }
}
elseif ($request['action'] == "addtocartremove") {
	$customer_id=$request['customer_id'];
	$quote_id=$request['quote_id'];
	$product_id=$request['product_id'];
	$sku=$request['sku'];
	$qty=$request['qty'];
	$connection2 = Mage::getSingleton('core/resource')->getConnection('core_read');
 $sql2 = "SELECT * FROM sales_flat_quote_item where cart_user_id='$customer_id' && sku='$sku'";

 $rowssku       = $connection2->fetchAll($sql2);
$qty=array();
	foreach($rowssku as $fetchrowssku){
		 $qty[]=$fetchrowssku['qty'];
	}
	try {
		$qtycart= $qty['0'];
		Mage::getSingleton('core/session')->unsMyValue();
	 $qtycarvalue=Mage::getSingleton('core/session')->setMyValue($qtycart);
	$cartremove = $client->shoppingCartProductRemove($session, $quote_id, array(array(
'product_id' => $product_id,
'sku' => $sku,
'qty' => $qty,
'options' => null,
'bundle_option' => null,
'bundle_option_qty' => null,
'links' => null
)));   
 $test=Mage::getSingleton('core/session')->getMyValue();
	                     $result5['data'] = $test;
						 $result5['data'][0] = $test;
					   //exit();
                        $result5['status'] = true;
                        $result5['message'] = 'Item removed from cart successfully.';	 
                        echo json_encode($result5);
                        }catch( Exception $e ) {
		$result5['data'] = null;
		$result5['status'] = false;
		$result5['message'] = $e->getMessage();
		echo json_encode($result5);
		
	}


}
elseif ($request['action'] == "addtocartqtyupdate") {
		$customer_id= $request['customer_id'];
	$sku= $request['sku'];
	$qty= $request['qty'];
	$quote_id= $request['quote_id'];
	$product_id= $request['product_id'];
	$connection2 = Mage::getSingleton('core/resource')->getConnection('core_read');
			 $sql1= "SELECT qty FROM sales_flat_quote_item where sku='$sku' and cart_user_id='$customer_id'";

 $rowssku1 = $connection2->fetchAll($sql1);
 //echo $rowssku1['qty'][0];
 $qtypre=array();
 foreach($rowssku1 as $rowssku2) {
	  $qtypre[]=$rowssku2['qty'];
 }
 $preqty= $qtypre[0];
	try{
        
		$addtocartqty = $client->shoppingCartProductUpdate($session, $quote_id, array(array(
'product_id' => $product_id,
'sku' => $sku,
'qty' => $qty,
'options' => null,
'bundle_option' => null,
'bundle_option_qty' => null,
'links' => null
)));   
	    $result['data'] = $preqty;
		$result['status'] = true;
        $result['message'] = 'Quantity updated successfully.';
		
		echo json_encode($result);
	  
       } catch( Exception $e ) {
		  $result['data']=[];
		$result['status'] = false;
        $result['message'] = $e->getMessage();
         echo json_encode($result);
	}

}
 else if($request['action'] == "login" ) {
	 $email = $request->email;
	 $password = $request->password;

         $session = Mage::getSingleton( 'customer/session' );
        Mage::app()->setCurrentStore(1);
        Mage::app()->getStore()->setWebsiteId(1);
        try
        {
            $session->login($email, $password);
            $customer = $session->getCustomer();
            $quoteCollection = Mage::getModel('sales/quote')->getCollection();
            $quoteCollection->addFieldToFilter('customer_id', $customer->getId());
            $quoteCollection->addOrder('updated_at');
            $quote = $quoteCollection->getFirstItem();
	
            echo json_encode(array('status' => true, 'data' => $customer->getId() , 'message' =>'Sign in successful.'  ));
        }
        catch( Exception $e )
        {
            echo json_encode(array('status' => false, 'message' => 'Invalid sign in credentials.'));
        }
} 
///////For Login Section Api end////
else if($request['action'] == "forgetpass" ) {
	$_userData1=file_get_contents("php://input");

    $request= json_decode($_userData1, false);
  
    if(!empty($email)) {
		
      $email = $request->email;
      $customer = Mage::getModel('customer/customer')
        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
        ->loadByEmail($email);
        //echo $customer->getId();
       if ($customer->getId()) {

        try {
            $newResetPasswordLinkToken =  Mage::helper('customer')->generateResetPasswordLinkToken();
            $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
            $customer->sendPasswordResetConfirmationEmail();
            $result = array('status'=> true,'message'=> 'A password change link has been sent to your email.');
             echo json_encode($result);
            } catch (Exception $exception) {
             //echo "Exception";
                Mage::log($exception);
        }
     }
   else {
    
     $result = array('status'=> false,'message'=> 'Invilid email.');
    echo json_encode($result);
    }

  }
  else {
    $result = array('status'=> false,'message'=> 'Invilid email.');
    echo json_encode($result);
  }

}

else if($request['action'] == "categorylist" ) {
	 try {
		 $result1 = $client->catalogCategoryTree($session);
		 
		$result['data'] = $result1;
		$result['status'] = true;
		$result['message'] = 'Get list of data successfully.';	 
		echo json_encode($result);
	 } catch( Exception $e ) {
		$result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
		
	}


}
else if($request['action'] == "productbyid" ) {
$request2= json_decode($_userData, false);
	 try {
		 $result1 = $client->catalogCategoryAssignedProducts($session, $productid);

		 foreach($result1 as $aneelBal){
			 $anlproductBal=Mage::getModel('catalog/product')->load($aneelBal->product_id);
			 $resulttt = $anlproductBal->getData();
			  $result['data'][]=$resulttt;
	
		 }

		 if (!empty($result)) {
		 	$result['status'] = true;
			$result['message'] = 'Get list of data successfully.';
		 	echo json_encode($result);
		 }else{
		 	$result['data'] = [];
                        $result['status'] = true;
                        $result['message'] = 'Empty result';
		 	echo json_encode($result);
		 }
	
	 } catch( Exception $e ) {
		$result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
		
	}

}

else if($request['action'] == "barsearch" ) {
	
         $filters = [];
        if($request['barcodesearch']!= '') {
            $filters = array(
                'complex_filter' => array(
                    [
                       'key' => 'barcode',
                        'value' => array('key' => 'like', 'value' => '%'.$request['barcodesearch'].'%') 
                    ],
                    [
                       'key' => 'status',
                        'value' => array('key' => 'eq', 'value' => 1) 
                    ]
                )
            );
        }
        
        
    
	 try {
		 $result1 = $client->catalogProductList($session, $filters);
                 
                 if(!empty($result1)) {
                          foreach($result1 as $aneelBal){
                                 $anlproductBal=Mage::getModel('catalog/product')->load($aneelBal->product_id);
                                 $resulttt = $anlproductBal->getData();
                                  $result['data'][]=$resulttt;
                         }

                        $result['status'] = true;
                        $result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result);
                 } else {
                        $result['data'] = [];
                        $result['status'] = true;
                        $result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result);
                 }
		//var_dump($result);	
	 } catch( Exception $e ) {
		$result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
		
	}


}

else if($request['action'] == "search" ) {
	if($request['text']!= '') {
        // Name
        $filters_name = [];
        $filters_name = array(
            'complex_filter' => array(
				[
                   'key' => 'name',
                   'value' => array('key' => 'like', 'value' => '%'.$request['text'].'%'),
                ], 
                [
                   'key' => 'status',
                   'value' => array('key' => 'eq', 'value' => 1) 
                ]
            )
        );
    	$result_name = $client->catalogProductList($session, $filters_name);

		// SKU
        $filters_sku = [];
        $filters_sku = array(
            'complex_filter' => array(
				[
                   'key' => 'sku',
                   'value' => array('key' => 'eq', 'value' => $request['text']),
                ], 
                [
                   'key' => 'status',
                   'value' => array('key' => 'eq', 'value' => 1) 
                ]
            )
        );
        $result_sku = $client->catalogProductList($session, $filters_sku);
		
    	// Description
		$filters_desc = [];
		$filters_desc = array(
			'complex_filter' => array(
				[
					'key' => 'description',
					'value' => array('key' => 'like', 'value' => '%'.$request['text'].'%') 
				],
				[
					'key' => 'status',
					'value' => array('key' => 'eq', 'value' => 1) 
				]
			)
		);
		$result_desc = $client->catalogProductList($session, $filters_desc);

		// Parte
		$filters_parte = [];
		$attributeOptionCode= $request['text'];

		$attr = 'Parte';
		$_product = Mage::getModel('catalog/product');
		$attr = $_product->getResource()->getAttribute($attr);
		if ($attr->usesSource()) {
		    $attrib_option_id = $attr->getSource()->getOptionId($attributeOptionCode);
		}
		if ($attrib_option_id) {
			$filters_parte = array(
                'complex_filter' => array(
                    [
                       'key' => 'parte',
                        'value' => array('key' => 'like', 'value' => '%'.$attrib_option_id.'%') 
                    ],
                    [
                       'key' => 'status',
                        'value' => array('key' => 'eq', 'value' => 1) 
                    ]
                )
            );
            $result_parte = $client->catalogProductList($session, $filters_parte);
		}else{
			$result_parte = array();
		}

		// Category incomplete
		$filters_desc = [];
		$category = Mage::getResourceModel('catalog/category_collection')
				    ->addFieldToFilter('name', $request['text'])
				    ->getFirstItem(); // category
		$categoryId = $category->getId();
	
		if ($categoryId) {
			//$result_catt = $client->catalogCategoryAssignedProducts($session, $categoryId);

			$allProductValue = $client->catalogCategoryAssignedProducts($session, $categoryId);
			$result = array();
			if(!empty($allProductValue)) {
			    for($i = 0;$i < count($allProductValue);$i++) {
			        $product_id = $allProductValue[$i]->product_id;
			        /* get product info from product id */
			        $attributes = new stdclass();
			        //$attributes->attributes = array('name', 'short_description', 'price');
			        //$attributes->additional_attributes = array('manufacturer');
			        $attributes->attributes = array('name');

			        $productInfo = $client->catalogProductInfo($session, $product_id,'1',$attributes);
			        $product_info = get_object_vars($productInfo);
			        /*$manufacturer = get_object_vars($product_info['additional_attributes'][0]);
			        if($manufacturer['value'] == $brand_id)
			        {*/
			            //array_push($result,$product_info);
			        //}
			        	$result_catt[] = (object)$product_info;
			    }
			
			} else {
			    $result_catt = array('Message' => 'No Products founds of this Category');
			}

		}else{
			$result_catt = array();
		}

		$result1 = array_unique(array_merge($result_desc, $result_sku, $result_name, $result_parte, $result_catt), SORT_REGULAR);

	}
	 try {

         if(!empty($result1)) {
                  foreach($result1 as $aneelBal){
                         $anlproductBal=Mage::getModel('catalog/product')->load($aneelBal->product_id);
                         $resulttt = $anlproductBal->getData();
                          $result['data'][]=$resulttt;
                 }

                $result['status'] = true;
                $result['message'] = 'Get list of search data successfully.';	 
                echo json_encode($result);
         } else {
                $result['data'] = [];
                $result['status'] = true;
                $result['message'] = 'No Result Found';	 
                echo json_encode($result);
         }
		//var_dump($result);	
	 } catch( Exception $e ) {
		$result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
	}
}

else if($request['action'] == "addtocart123" ) {
 
	try {
$product_ids = [549,339,549];
  /* Get Product id From Form Post */
    foreach($product_ids as $key=>$product_id){
        /* Add Product in to Cart */
        $product= Mage::getModel('catalog/product');
        $product->load($product_ids[$key]); // Product Id
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $quote->addProduct($product); // quantity is 1
        $cart = Mage::getSingleton('checkout/cart');
        $cart->init(); // tried commenting this too!
        $cart->save();
        Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        $quote->collectTotals()->save();
        
    } 
	  var_dump($quote);

    }
	catch( Exception $e ) {
		$result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
	}
    
    

}

elseif ($request['action'] == "cartproductlist") {
    
    //$quoteId = $request['quoteId'];
    $quoteId = $request['quoteid'];
    
    try{
        $result = $client->shoppingCartProductList($session, $quoteId);
        var_dump($result);
    } catch( Exception $e ) {
		//$result['data'] = null;
		//$result['status'] = false;
        
		//$result['message'] = $e->getMessage();
		//echo json_encode($result);
                
                print_r($e->getMessage());
                
		
	}
}

elseif ($request['action'] == "productinfo") {
    
  
     $productid= $request['sku'];
	
	
    
    try{
		 
		$anlproductBal=Mage::getModel('catalog/product')->loadByAttribute('sku',$productid);
        $resulttt = $anlproductBal->getData();
		//echo $anlproductBal->getQty();
		
		//echo $anlproductBal->getQty();
		//$a=array("stock"=>"1","b"=>"green");
     //$tt = array_push($a, $resulttt);
	

	   
	   
		$result['data']=$resulttt;
		if($anlproductBal->getStockItem()->getIsInStock()==1){
			$result['stock'] = '1';
		}else{
			$result['stock'] = '0';
		}
		$result['status'] = true;
        $result['message'] = 'Get list of search data successfully.';
		
	   //echo "<pre>";
	  //print_r($result);


		echo json_encode($result);
        
        //var_dump($resulttt);
    } catch( Exception $e ) {
	
        $result['data']=[];
		$result['status'] = false;
        $result['message'] = $e->getMessage();
         echo json_encode($resulttt);        
                
		
	}
}

elseif ($request['action'] == "addtocart456") {
	$customer_id= $request['customer_id'];
	$sku= $request['sku'];
	$qty= $request['qty'];
	$product_id= $request['product_id'];
	$name= $request['name'];
//$writeConnection->query($query);
$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
$sql        = "SELECT * FROM sales_flat_quote_item where cart_user_id='$customer_id'";

$rowssku       = $connection->fetchAll($sql);
$sku1=array();
//$qty1=array();
	foreach($rowssku as $fetchrowssku){
		$sku1[]=$fetchrowssku['sku'];
		//$qty1[]=$fetchrowssku['qty'];
		
	}
	
	if(in_array($sku, $sku1)){
		 $sql1        = "SELECT * FROM sales_flat_quote_item where sku='$sku' and cart_user_id='$customer_id'";

$rowssku1       = $connection->fetchAll($sql1);
//print_r($rowssku1);
$qty1=array();
foreach($rowssku1 as $fetchrowssku1){
		 $qty1[]=$fetchrowssku1['qty'];
		//$qty1[]=$fetchrowssku['qty'];
		
	}

      $qtyincrement=$qty+$qty1[0];
     $write = Mage::getSingleton('core/resource')->getConnection('core_write');
    $write->update("sales_flat_quote_item",array("qty" => $qtyincrement),"sku='$sku'");
        
        

		}else{
		$quot_id = $client->shoppingCartCreate($session,1);
	    $customer_id= $request['customer_id'];
	   //$product_id= $request['product_id'];
	    $sku= $request['sku'];
        $qty= $request['qty'];
		$product_id= $request['product_id'];
		$name= $request['name'];
	$resource = Mage::getSingleton('core/resource');
      $writeConnection = $resource->getConnection('core_write');

	 
      //The Query
	  
         $query = "insert into sales_flat_quote_item
                       (quote_id,sku,cart_user_id, qty, product_id, name) 
                  values('$quot_id','$sku','$customer_id','$qty', '$product_id', '$name')";
        
        $query1= $writeConnection->query($query);	
		}
	
	   
        
		//exit();	
			
		

	try{
        
		
       
		
	    $result['data'] = $query1;
		$result['status'] = true;
        $result['message'] = 'Item added to cart successfully.';
		
		echo json_encode($result);
	  
       } catch( Exception $e ) {
		  $result['data']=[];
		$result['status'] = false;
        $result['message'] = $e->getMessage();
         echo json_encode($result);
                
		
	}
	

 
			  

}

elseif ($request['action'] == "cartqtyupdate") {
	$customer_id= $request['customer_id'];
	$sku= $request['sku'];
	$qty= $request['qty'];
//$writeConnection->query($query);
$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
$sql        = "SELECT * FROM sales_flat_quote_item where cart_user_id='$customer_id'";
$rowssku       = $connection->fetchAll($sql);
$sku1=array();
	foreach($rowssku as $fetchrowssku){
		$sku1[]=$fetchrowssku['sku'];
		
	}
	
	if(in_array($sku, $sku1)){
     $write = Mage::getSingleton('core/resource')->getConnection('core_write');
    $query2=$write->update("sales_flat_quote_item",array("qty" => $qty),"sku='$sku'");
    
		}
		try{
        
	    $result['data'] = $query2;
		$result['status'] = true;
        $result['message'] = 'Item added to cart successfully.';
		
		echo json_encode($result);
	  
       } catch( Exception $e ) {
		  $result['data']=[];
		$result['status'] = false;
        $result['message'] = $e->getMessage();
         echo json_encode($result);
                
		
	}
	

	
    

}
elseif ($request['action'] == "customeruserinfo") {
	//echo "hii";
	 $cuserid= $request['cuserid'];
	 $connection1 = Mage::getSingleton('core/resource')->getConnection('core_read');
     $sql2 = "SELECT 'qty', SUM(qty > 0), SUM(qty) FROM sales_flat_quote_item where cart_user_id = '$cuserid'";
 

 $rowssku= $connection1->fetchAll($sql2); 
 $rowsum=array();
foreach($rowssku as $rowsskuqty){
	$rowsum[]= $rowsskuqty['SUM(qty)'];
}
$result['sum_qty'] = $rowsum[0];

	try{
	$result1 = $client->customerCustomerInfo($session, $cuserid);
	
	    $result['data'] = $result1;
		$result['sum'] = $rowsum[0];
		$result['status'] = true;
		$result['message'] = 'Get customer information  successfully.';	 
		echo json_encode($result);
	}catch( Exception $e ) {
		$result['data']=[];
		$result['status'] = false;
        $result['message'] = $e->getMessage();
         echo json_encode($result);
	}
	
    

}

elseif ($request['action'] == "customerupdate") {
	//echo "hii";
	 $cuseruid= $request['cuseruid'];
	  $email= $request['email'];
	   $firstname= $request['firstname'];
	   $lastname= $request['lastname'];
	   //$password= $request['password'];
	 

	try{
		$result1 = $client->customerCustomerUpdate($session, $cuseruid, array('email' => $email, 'firstname' => $firstname, 'lastname' => $lastname, 'website_id' => 1, 'store_id' => 1, 'group_id' => 1));

//var_dump ($result);
	
	    $result['data'] = $result1;
		$result['status'] = true;
		$result['message'] = 'Profile updated successfully.';	 
		echo json_encode($result);
	}catch( Exception $e ) {
		$result['data']=[];
		$result['status'] = false;
        $result['message'] = $e->getMessage();
         echo json_encode($result);
	}

    

}
elseif ($request['action'] == "parteaattribte") {
	try{
		$result1 = $client->catalogProductAttributeOptions($session, '212');
        //print_r($result1);
		array_splice($result1, 0, 1);
		//print_r($result1);
		//exit();
		//foreach($result1 as $key[]=>$clist){
		//	$clist
		//}
	    $result['data'] = $result1;
		$result['status'] = true;
		$result['message'] = 'Profile updated successfully.';	 
		echo json_encode($result);
	}catch( Exception $e ) {
		$result['data']=[];
		$result['status'] = false;
        $result['message'] = $e->getMessage();
         echo json_encode($result);
	}

  
}
elseif ($request['action'] == "modelocategorylist") {
	$parentCategoryId = 42;
$children = Mage::getModel('catalog/category')->load(42)->getChildrenCategories();

foreach ($children as $category1){


	$category1subid= $category1->getId();
	$category1subname= $category1->getName();
    $category = Mage::getModel('catalog/category')->load($category1->getId())->getChildrenCategories();
    //echo '<li><a href="' . $category->getUrl() . '">' . $category->getName() . '</a></li>';
	foreach($category as $subcate){
		 $subcatid=$subcate->getId();
		 $subcatname=$subcate->getName();

	$result21['allsubs'] = array(
		'catesubchildid' => $subcatid,
		'catesubchildname' => $subcatname
		);


		$allcatss[] = array(
		'catid' => $category1subid,
		'catname' => $category1subname,
		'sub' => $result21['allsubs'],
		);


	}

}

	try{

	    $result['data'] = $allcatss;
		$result['status'] = true;
		$result['message'] = 'Category list getting successfully.';	 
		echo json_encode($result);
	}catch( Exception $e ) {
		$result['data']=[];
		$result['status'] = false;
        $result['message'] = $e->getMessage();
         echo json_encode($result);
	}

  
}
elseif ($request['action'] == "addvancedsearch") {
	
	 $marca= $request['marca'];
	 $modulo= $request['modulo'];
	  $parta= $request['parta'];
	 
	 if($marca == null && $modulo == null && $parta == null) {
		// $result1 = $client->catalogCategoryAssignedProducts($session);
		$category_id=42;
		$products = Mage::getModel('catalog/category')->load($category_id)->getProductCollection()->addAttributeToSelect('*')->addAttributeToFilter('status', 1); 
 $mamopa=array();
  foreach($products as $procollection){
	//echo $procollection['sku'];
	$productee1 = Mage::getModel('catalog/product')->loadByAttribute('sku', $procollection['sku']);
$mamopa[]=$productee1->getData();	

	
	
	}

		try {            
		                if(!empty($mamopa)){
		               $result['data'] = $mamopa;
                        $result['status'] = true;
                        $result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result);
                 }else{
					$result['data'] = [];
                        $result['status'] = true;
                        //$result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result); 
				 }
                 
		//var_dump($result);	
	 } catch( Exception $e ) {
		$result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
		
	}
         
	 }
	 
elseif($modulo!= null && $parta!= null && $marca!= null) {

	  $attributeCode = 'parte';
//echo "chi";exit();
$collection1 = Mage::getModel('catalog/product')->getCollection();
$collection1->addAttributeToFilter('status', 1);
$collection1->addAttributeToFilter($attributeCode,$parta);
//$collection1->addAttributeToFilter('category_id', array('in' => array($marca)));

 $collection1->joinField(
        'category_id', 'catalog/category_product', 'category_id', 
        'product_id = entity_id', null, 'left'
    )->addAttributeToSelect('*')->addAttributeToFilter('category_id', $modulo);
    $modparta=array();
    foreach($collection1->getData() as $attcollection){
	//echo $attcollection['sku'];
	$productee2 = Mage::getModel('catalog/product')->loadByAttribute('sku', $attcollection['sku']);
	$modparta[]=$productee2->getData();
//$result['data'][]=$productee2->getData();
	
	
	}
  

	try {	 
		             if(!empty($modparta)){
	                   $result['data'] = $modparta;
					   $result['status'] = true;
                        $result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result);
					 }else{
						$result['data'] =[];
					   $result['status'] = true;
                        //$result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result); 
					 }
   } catch( Exception $e ) {
		$result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
		
	}
	 
	 
} 	 
	 //marca modulo start
elseif($marca!= null && $modulo!=null) {
	


try {
                $_productCollection = Mage::getModel('catalog/product')
                 ->getCollection()
                 ->joinTable('catalog_category_product', 'product_id=entity_id', array('category_id'=>'category_id'), null, 'left')
                 ->addAttributeToFilter('category_id', array('in' => array('finset' => $modulo)))
                 ->addAttributeToSelect('*');
                 //->setPageSize(5);
				 $marcaparta=array();
				foreach($_productCollection->getData() as $attcollectionp){
	//echo $attcollection['sku'];
	$productee3 = Mage::getModel('catalog/product')->loadByAttribute('sku', $attcollectionp['sku']);
	$marcaparta[]=$productee3->getData();
    //$result['data'][]=$productee2->getData();
	//print_r($productee3->getData());
	
	
	}
if(!empty($marcaparta)) {
$result['data'] = $marcaparta;
$result['status'] = true;
$result['message'] = 'Get list of search data successfully.';	 
echo json_encode($result);
} else {
$result['data'] = [];
$result['status'] = true;
$result['message'] = 'Get list of search data successfully.';	 
echo json_encode($result);
}
//var_dump($result);	
} catch( Exception $e ) {
$result['data'] = null;
$result['status'] = false;
$result['message'] = $e->getMessage();
echo json_encode($result);

}
}	 

//marca modulo end
//marca parta start
elseif($marca!= null && $parta!= null) {
	 //$marca= $request['marca'];
	// $modulo= $request['modulo'];
	  //$parta= $request['parta'];
	  $attributeCode = 'parte';
//echo "chi";exit();
$collection1 = Mage::getModel('catalog/product')->getCollection();
$collection1->addAttributeToFilter('status', 1);
$collection1->addAttributeToFilter($attributeCode,$parta);
//$collection1->addAttributeToFilter('category_id', array('in' => array($marca)));
$marcaparta=array();
 $collection1->joinField(
        'category_id', 'catalog/category_product', 'category_id', 
        'product_id = entity_id', null, 'left'
    )->addAttributeToSelect('*')->addAttributeToFilter('category_id', $marca);
    
    foreach($collection1->getData() as $attcollection){
	//echo $attcollection['sku'];
	$productee2 = Mage::getModel('catalog/product')->loadByAttribute('sku', $attcollection['sku']);
	$marcaparta[]=$productee2->getData();
    //$result['data'][]=$productee2->getData();
	
	
	}
  

	try {	 
		              if(!empty($marcaparta)) {
	                   $result['data'] = $marcaparta;
					   $result['status'] = true;
                        $result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result);
                       }else {
						 $result['data'] = [];
					   $result['status'] = true;
                        //$result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result);   
					   }
					   
   } catch( Exception $e ) {
		$result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
		
	}
	 
	 
} 
//marca parta end


 elseif($marca!= null) {
		 //$result = $client->catalogCategoryAssignedProducts($session, $marca);
		 try {
			 $result1 = $client->catalogCategoryAssignedProducts($session, $marca);
		 //$result1 = $client->catalogProductList($session,$marca);
                 //print_r($$result1);
						// exit();
                         $resulttt=array();
                          foreach($result1 as $productlist){
                                 $allproduct=Mage::getModel('catalog/product')->load($productlist->product_id);
                                 $resulttt[] = $allproduct->getData();
                                  //$result['data'][]=$resulttt;
                         }
						 
                     if(!empty($resulttt)) {
						 $result['data'] = $resulttt;
                        $result['status'] = true;
                        $result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result);
                 } else {
                        $result['data'] = [];
                        $result['status'] = true;
                        $result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result);
                 }
		//var_dump($result);	
	 } catch( Exception $e ) {
		$result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
		
	}
}

	elseif($modulo!= null) {
		 //$result = $client->catalogCategoryAssignedProducts($session, $marca);
		 try {
			 $result1 = $client->catalogCategoryAssignedProducts($session, $modulo);
		 //$result1 = $client->catalogProductList($session,$marca);
                 
                
					 $resulttt=array();
					 
                          foreach($result1 as $productlist){
                                 $allproduct=Mage::getModel('catalog/product')->load($productlist->product_id);
                                 $resulttt[] = $allproduct->getData();
                                 // $result['data'][]=$resulttt;
                         }
                        if(!empty($resulttt)) {
							$result['data'] =  $resulttt;
                        $result['status'] = true;
                        $result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result);
                 } else {
                        $result['data'] = [];
                        $result['status'] = true;
                        $result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result);
                 }
		//var_dump($result);	
	 } catch( Exception $e ) {
		$result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
		
	}
} elseif($parta!= null) {
	 
$attributeCode = 'parte';

$collection = Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToFilter('status', 1);
$collection->addAttributeToFilter($attributeCode,$parta);
//$allproduct=Mage::getModel('catalog/product')->load($collection);
//echo "<pre>";
//print_r($collection->getData());
//echo $allproduct->entity_id;
//exit;
    //$attributedata= $collection->getData();
	$parta1 = array();
	foreach($collection->getData() as $attcollection){
	//echo $attcollection['sku'];
	$productee = Mage::getModel('catalog/product')->loadByAttribute('sku', $attcollection['sku']);
	$parta1[]=$productee->getData();
//$result['data'][]=$productee->getData();
	
	
	}
	
	
		 
		 try {
			          if(!empty($parta1)){
                        $result['data']=$parta1;
                        $result['status'] = true;
                        $result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result);
                 }else{
					  $result['data']=[];
                        $result['status'] = true;
                        $result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result);
				 }
		//var_dump($result);	
	 } catch( Exception $e ) {
		$result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
		
	}
	
}



else {
		  $result = $client->catalogCategoryAssignedProducts($session, $marca);
		echo "<pre>";
		print_r($result);
	 }
	 


	 
  
  
}

elseif ($request['action'] == "shoppingCartCustomer") {
	
	$billing= $request['billing'];
	$quote_id= $request['quote_id'];
	$firstname= $request['firstname'];
	$lastname= $request['lastname'];
	$street= $request['street'];
	$city= $request['city'];
	$region= $request['region'];
	$postcode= $request['postcode'];
	$country_id= $request['country_id'];
	$telephone= $request['telephone'];
	
	if($billing=='billing'){
            try 
            {
 $result2 = $client->shoppingCartCustomerAddresses($session, $quote_id, array(array(
'mode' => $billing,
'firstname' => $firstname,
'lastname' => $lastname,
'street' => $street,
'city' => $city,
'region' => $region,
'postcode' => $postcode,
'country_id' => $country_id,
'telephone' => $telephone,
'is_default_billing' => 0
)));   
                        $result['data']=$result2;
                        $result['status'] = true;
                        $result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result);
              
	
            } 
            catch( Exception $e )
            {
          $result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
            }
}else {
	try 
            {
 $result2 = $client->shoppingCartCustomerAddresses($session, $quote_id, array(array(
'mode' => 'shipping',
'firstname' => $firstname,
'lastname' => $lastname,
'street' => $street,
'city' => $city,
'region' => $region,
'postcode' => $postcode,
'country_id' => $country_id,
'telephone' => $telephone,
'is_default_shipping' => 0
)));   
                        $result['data']=$result2;
                        $result['status'] = true;
                        $result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result);
              
	
            } 
            catch( Exception $e )
            {
          $result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
            }
	
}			
			
        

}

elseif ($request['action'] == "customerAddressCreateapi") {
	
	$customer_id= $request['customer_id'];
	//$quote_id= $request['quote_id'];
	$firstname= $request['firstname'];
	$lastname= $request['lastname'];
	$street_line_1= $request['street_line_1'];
	$street_line_2= $request['street_line_2'];
	$city= $request['city'];
	$region= $request['region'];
	$region_id= $request['region_id'];
	$postcode= $request['postcode'];
	$country_id= $request['country_id'];
	$telephone= $request['telephone'];
	$is_default_billing= $request['is_default_billing'];
	$is_default_shipping= $request['is_default_shipping'];
            try 
            {
				
				$result2 = $client->customerAddressCreate($session, $customer_id, array('firstname' => $firstname, 'lastname' => $lastname, 'street' => array($street_line_1, $street_line_2), 'city' => $city, 'country_id' => $country_id, 'region' => $region, 'region_id' => $region_id, 'postcode' => $postcode, 'telephone' => $telephone, 
				'is_default_billing' => true, 'is_default_shipping' => FALSE));

//var_dump ($result2);
 
                        $result['data']=$result2;
                       $result['status'] = true;
                       $result['message'] = 'Address updated successfully.';	 
                       echo json_encode($result);
              
	
            } 
            catch( Exception $e )
            {
          $result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
            }

			
        

}

elseif ($request['action'] == "customerdirectoryCountryList") {
	
	
	
	
            try 
            {
             $result2 = $client->directoryCountryList($session);
//var_dump($result);
 
                        $result['data']=$result2;
                        $result['status'] = true;
                        $result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result);
              
	
            } 
            catch( Exception $e )
            {
          $result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
            }
	
			
        

}

elseif ($request['action'] == "customerdirectoryRegionList") {
	
	
	$country= $request['country'];
	
            try 
            {
             $result2 = $client->directoryRegionList($session,$country);
              //var_dump($result);
                 //var_dump($result);
 
                        $result['data']=$result2;
                        $result['status'] = true;
                        $result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result);
              
	
            } 
            catch( Exception $e )
            {
          $result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
            }
	
			
        

}
elseif ($request['action'] == "customeraddressinfo") {
	
	$customer_id= $request['customer_id'];
	
	//$addresses = $cli->customerAddressList($session_id, $customer->customer_id);
            try 
            {
            $result2 = $client->customerAddressList($session, $customer_id);
			
			
//var_dump($result);
 
                        $result['data']=$result2;
                        $result['status'] = true;
                        $result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result);
              
	
            } 
            catch( Exception $e )
            {
          $result['data'] = [];
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
            }
	
			
        

}

elseif ($request['action'] == "customeraddressinfomation") {
	
	$customeraddress_id= $request['customeraddress_id'];
	
	//$addresses = $cli->customerAddressList($session_id, $customer->customer_id);
            try 
            {
            $result2 = $client->customerAddressInfo($session, $customeraddress_id);
			
			
//var_dump($result);
 
                        $result['data']=$result2;
                        $result['status'] = true;
                        $result['message'] = 'Get list of search data successfully.';	 
                        echo json_encode($result);
              
	
            } 
            catch( Exception $e )
            {
          $result['data'] = [];
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
            }
	
			
        

}


elseif ($request['action'] == "customeraddressinfoupdate") {
	
	$quote_id= $request['quote_id'];
	//$address_type= $request['address_type'];

	    $resource = Mage::getSingleton('core/resource');
      	$writeConnection = $resource->getConnection('core_write');
		$sql        = "SELECT * FROM sales_flat_quote_address where quote_id='$quote_id'";
		$rowsshippingaddress     = $writeConnection->fetchAll($sql);

        try {
  		
		    

  			$result['data'] = $rowsshippingaddress;
	  		$result['status'] = true;
        	$result['message'] = 'Get Shipping Address Successfully';  
  			echo json_encode($result);

   	} 
    catch( Exception $e )
    {
          $result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
    }

}

else if($request['action'] == "addtocart"){
 

    $customer_id= $request['customer_id'];
 $sku= $request['sku'];
 $qty= $request['qty'];
 $product_id= $request['product_id'];
 $name= $request['name'];



 $resource = Mage::getSingleton('core/resource');
      $writeConnection = $resource->getConnection('core_write');
$sql        = "SELECT * FROM sales_flat_quote_item where cart_user_id='$customer_id' ORDER BY item_id DESC LIMIT 1";

$rowssku       = $writeConnection->fetchAll($sql);


$sku1=array();
//$qty1=array();
 foreach($rowssku as $fetchrowssku){
  $sku1[]=$fetchrowssku['sku'];
  //$qty1[]=$fetchrowssku['qty'];
  
 }

$customerid1=array();
$qutoe1=array();
//$qty1=array();
 foreach($rowssku as $fetchrowssku){
  $customerid1[]=$fetchrowssku['cart_user_id'];
  $qutoe1[]=$fetchrowssku['quote_id'];
  //$qty1[]=$fetchrowssku['qty'];
  
 }


 
 if(in_array($sku, $sku1)){
   $sql1        = "SELECT * FROM sales_flat_quote_item where sku='$sku' and cart_user_id='$customer_id'";

  $rowssku1       = $writeConnection->fetchAll($sql1);
  //print_r($rowssku1);
  $qty1=array();
  foreach($rowssku1 as $fetchrowssku1){
     $qty1[]=$fetchrowssku1['qty'];
    //$qty1[]=$fetchrowssku['qty'];
    
   }
  //$qty2=$qty1['qty'];
  //echo 'hiii'.$qty1[0];
  //exit();
        $qtyincrement=$qty+$qty1[0];
       $write = Mage::getSingleton('core/resource')->getConnection('core_write');
      $write->update("sales_flat_quote_item",array("qty" => $qtyincrement),"sku='$sku'");
          
        

  }

 else if(in_array($customer_id, $customerid1)){
   $sql1        = "SELECT * FROM sales_flat_quote_item where cart_user_id='$customer_id'  ORDER BY item_id DESC LIMIT 1";

  $rowssku1       = $writeConnection->fetchAll($sql1);
  // print_r($rowssku1);
  // exit;
  $qutoe1=array();
  foreach($rowssku1 as $fetchrowssku1){
     $qutoe1[]=$fetchrowssku1['quote_id'];
    //$qty1[]=$fetchrowssku['qty'];
    
   }

       $quoteupdate=$qutoe1[0];
     
       $resource = Mage::getSingleton('core/resource');
          $writeConnection = $resource->getConnection('core_write');

        //The Query
     
           $queryn = "insert into sales_flat_quote_item
                         (quote_id,sku,cart_user_id, qty, product_id, name) 
                    values('$quoteupdate','$sku','$customer_id','$qty', '$product_id', '$name')";
          
          $queryn1= $writeConnection->query($queryn);

          //exit;
      }

      else if (in_array($sku, $sku1) && in_array($customer_id, $customerid1)){

        $sql1        = "SELECT * FROM sales_flat_quote_item where sku='$sku' and cart_user_id='$customer_id'  ORDER BY item_id DESC LIMIT 1";

     $rowssku1       = $writeConnection->fetchAll($sql1);
 
     $qutoe1=array();
     $qty1=array();
     foreach($rowssku1 as $fetchrowssku1){
        $qutoe1[]=$fetchrowssku1['quote_id'];
        $qty1[]=$fetchrowssku1['qty'];
       //$qty1[]=$fetchrowssku['qty'];
       
      }

          $quoteupdate=$qutoe1[0];
          $qtyincrement=$qty+$qty1[0];
        
          $resource = Mage::getSingleton('core/resource');
             $writeConnection = $resource->getConnection('core_write');

           //The Query
        
             $write = Mage::getSingleton('core/resource')->getConnection('core_write');
          $write->update("sales_flat_quote_item",array("qty" => $qtyincrement),array("quote_id" => $quoteupdate),"sku='$sku'");


      }

        else{


          $quot_id = $client->shoppingCartCreate($session,1);
		  //echo $quot_id;
		  //exit();
       $customer_id= $request['customer_id'];
      //$product_id= $request['product_id'];
       $sku= $request['sku'];
          $qty= $request['qty'];
    $product_id= $request['product_id'];
    $name= $request['name'];
   $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');

    
        //The Query
     
           $query = "insert into sales_flat_quote_item
                         (quote_id,sku,cart_user_id, qty, product_id, name) 
                    values('$quot_id','$sku','$customer_id','$qty', '$product_id', '$name')";
          
          $query1= $writeConnection->query($query);
   }


    try{
        
  
     $result['quote_id'] = $qutoe1;
     //$result['quote_id'] = $quot_id;
  $result['status'] = true;
        $result['message'] = 'New item added to cart successfully.';
  
  echo json_encode($result);
   
       } catch( Exception $e ) {
   $result['data']=[];
  $result['status'] = false;
        $result['message'] = 'Item added to cart successfully.';
         echo json_encode($result);
  
 }



}


elseif ($request[0]['action'] == "customershippinaddressinfo1") {
	
	
	$quote_id= $request[0]['quote_id'];
	//$customer_id= $request['customer_id'];
	$billing= $request[1]['billmode'];
	//$firstname= $request[1]['firstname'];
	//$billing
	echo "<pre>";
	print_r($billing);
	exit;
	
	$shipping= $request[2]['shippinglmode'];
	$shipping= $request[2]['shippinglmode'];
	//$addresses = $cli->customerAddressList($session_id, $customer->customer_id);
try 
  {
if (in_array("billing", $billing))
  {
 
$bill = array($billing);
echo "hii<pre>";
	print_r($bill);
	exit;
}

else
  {
 $bill = array(array(
'mode' => 'billing',
'firstname' => 'Aneel1',
'lastname' => 'Kumar',
'street' => 'kol',
'city' => 'kol',
'region' => 'WB',
'postcode' => '123456',
'country_id' => 'IND',
'telephone' => '123456789',
'is_default_billing' => 1
));
  }
if (in_array("shipping", $shipping))
  {
$bill = array(array(
'mode' => 'shipping',
'firstname' => 'Aneel166',
'lastname' => 'Kumar66',
'street' => 'kol',
'city' => 'kol',
'region' => 'WB',
'postcode' => '123456',
'country_id' => 'IND',
'telephone' => '123456789',
'is_default_billing' => 1
));
echo "<pre>";
	print_r($bill);
		
}else {
$ship = array(array(
'mode' => 'shipping',
'firstname' => 'Aneel166',
'lastname' => 'Kumar66',
'street' => 'kol',
'city' => 'kol',
'region' => 'WB',
'postcode' => '123456',
'country_id' => 'IND',
'telephone' => '123456789',
'is_default_billing' => 1
));	
}
exit;
if($bill==$ship){
	
	$result1 = $client->shoppingCartCustomerAddresses($session, 981, array(array(
'mode' => 'billing',
'firstname' => 'Aneel1',
'lastname' => 'Kumar',
'street' => 'kol',
'city' => 'kol',
'region' => 'WB',
'postcode' => '123456',
'country_id' => 'IND',
'telephone' => '123456789',
'is_default_billing' => 1
)));  


$result2 = $client->shoppingCartCustomerAddresses($session, 981, array(array(
'mode' => 'shipping',
'firstname' => 'Aneel',
'lastname' => 'Kumar',
'street' => 'kol',
'city' => 'kol',
'region' => 'WB',
'postcode' => '123456',
'country_id' => 'IND',
'telephone' => '123456789',
'is_default_billing' => 1
)));  


}else{
$result1 = $client->shoppingCartCustomerAddresses($session, 981, array(array(
'mode' => 'billing',
'firstname' => 'Aneel1',
'lastname' => 'Kumar',
'street' => 'kol',
'city' => 'kol',
'region' => 'WB',
'postcode' => '123456',
'country_id' => 'IND',
'telephone' => '123456789',
'is_default_billing' => 1
)));  


$result2 = $client->shoppingCartCustomerAddresses($session, 981, array(array(
'mode' => 'shipping',
'firstname' => 'Aneel1',
'lastname' => 'Kumar',
'street' => 'kol',
'city' => 'kol',
'region' => 'WB',
'postcode' => '123456',
'country_id' => 'IND',
'telephone' => '123456789',
'is_default_billing' => 1
)));  


}	

            } 
            catch( Exception $e )
            {
          $result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
            }
	
			
        

}

elseif ($request['action'] == "customershippinaddressinfo11") {
	
	
	$quote_id= $request['quote_id'];
	//$customer_id= $request['customer_id'];
	$billing= $request['billmode'];
	$shipping= $request['shippinglmode'];

try 
  {
if (in_array("billing", $billing))
  {
 
$bill = array($billing);
//echo "hii<pre>";
	//print_r($bill);
	//exit;
}

else
  {
 $bill = array(array(
'mode' => 'billing',
'firstname' => 'Aneel1',
'lastname' => 'Kumar',
'street' => 'kol',
'city' => 'kol',
'region' => 'WB',
'postcode' => '123456',
'country_id' => 'IND',
'telephone' => '123456789',
'is_default_billing' => 1
));
  }
if (in_array("shipping", $shipping))
  {
$ship = array($shipping);
//echo "hii<pre>";
	//print_r($ship);
		
}else {
$ship = array(array(
'mode' => 'shipping',
'firstname' => 'Aneel166',
'lastname' => 'Kumar66',
'street' => 'kol',
'city' => 'kol',
'region' => 'WB',
'postcode' => '123456',
'country_id' => 'IND',
'telephone' => '123456789',
'is_default_billing' => 1
));	
}
//exit;
if($bill==$ship){
	
	$result1 = $client->shoppingCartCustomerAddresses($session, $quote_id, $bill);  


$result2 = $client->shoppingCartCustomerAddresses($session,  $quote_id, $ship);  


}else{
$result1 = $client->shoppingCartCustomerAddresses($session,  $quote_id, $bill);  


$result2 = $client->shoppingCartCustomerAddresses($session,  $quote_id, $ship);  


}	
$result['data'] = $result1;
     //$result['quoteid'] = $quot_id;
  $result['status'] = true;
        $result['message'] = 'Address updated successfully';
  
  echo json_encode($result);

   } 
            catch( Exception $e )
            {
          $result['data'] = null;
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
            }
	
			
        

}


elseif ($request['action'] == "customershippinaddressinfo") {


  $quote_id= $request['quote_id'];
  $customer_id= $request['customer_id'];
  $billing= $request['billmode'];
  $shipping= $request['shippinglmode'];


try 
  {
if (in_array("billing", $billing))
  {
 
$bill = array($billing);

}

else
  {
 $bill = array(array(
            'mode' => 'billing',
            'firstname' => $customer['firstname'],
            'lastname' => $customer['lastname'],
            'street' => 'street address',
            'city' => 'city',
            'region' => 'region',
            'telephone' => 'phone number',
            'postcode' => 'postcode',
            'country_id' => 'country ID',
            'is_default_shipping' => 0,
            'is_default_billing' => 0
));
  }
if (in_array("shipping", $shipping))
  {
$ship = array($shipping);
//echo "hii<pre>";
	//print_r($ship);
		
}else {
$ship = array(array(
            'mode' => 'shipping',
            'firstname' => $customer['firstname'],
            'lastname' => $customer['lastname'],
            'street' => 'street address',
            'city' => 'city',
            'region' => 'region',
            'telephone' => 'phone number',
            'postcode' => 'postcode',
            'country_id' => 'country ID',
            'is_default_shipping' => 0,
            'is_default_billing' => 0
));	
}
//exit;
$address = array_merge($ship,$bill);

 
$result3 = $client->shoppingCartCustomerAddresses($session, $quote_id, $address);
$result4 = $client->shoppingCartShippingMethod($session, $quote_id, 'flatrate_flatrate');


  $result['data'] = $result3;
  $result['status'] = true;
  $result['message'] = 'Address updated successfully';  
  echo json_encode($result);

   } 
catch( Exception $e )
{
  $result['data'] = null;
	$result['status'] = false;
	$result['message'] = $e->getMessage();
	echo json_encode($result);
}

}


elseif ($request['action'] == "paymentsuccess") {

	$quote_id= $request['quote_id'];
	
	$transaction_id=$request['transaction_id'];
	$status=$request['status'];

 
      $paymentMethod = array(
      'method' => 'cashondelivery',
      'title' => 'Paypal Express Checkout',
      'cc_types' => 'NULL'
	  	);
     
	try{
   
     // add payment method
 if($transaction_id != "" && $status != "") {
    $result8 = $client->shoppingCartPaymentMethod($session, $quote_id, $paymentMethod);
	$orderId = $client->shoppingCartOrder($session, $quote_id, null, null);
	 $result['data'] = $orderId;
  $result['status'] = true;
  $result['message'] = 'Order Placed Successfully';  
  echo json_encode($result);
  
    }else {
	$result['data'] = null;
   $result['status'] = false;
   $result['message'] = 'Please check Payment details';
    echo json_encode($result);	
	}
 
 
  
   
   } 
catch( Exception $e )
{
   $result['data'] = null;
 $result['status'] = false;
 $result['message'] = 'Please check Payment details';
 echo json_encode($result);
}
 
 

}

elseif ($request['action'] == "orderinfo") {

$UserID= $request['customer_id'];
try{
  $filter = array('filter' => array(array('key' => 'customer_id', 'value' => $UserID)));		
  $result1 = (array)$client->salesOrderList($session,$filter);
 
   $order_id=array();
  
   $rescall=array();
  foreach($result1 as $key => $totalitem){

	 $order_id[]=$totalitem->order_id;
	 
	$order = Mage::getModel('sales/order')->load($totalitem->order_id);
	
	$items = $order->getAllItems();
	 
	$rescall=array();
	foreach($items as $allimages){
		//print_r($allimages->getData());
		 $ProductId=$allimages->getProductId();
		 $objproduct = Mage::getModel('catalog/product')->load($ProductId);
         //$_objproduct = $obj->load($ProductId);
       // $rescall[]=$objproduct->getImageUrl();
		$rescall[]=Mage::getModel('catalog/product_media_config')
        ->getMediaUrl( $objproduct->getImage());
		
	}
 $resAll[] = ['inc_id'=>$totalitem->increment_id, 'created_at'=>$totalitem->created_at, 'total_item_count'=>$totalitem->total_item_count, 'grand_total'=>$totalitem->grand_total, 'image'=>$rescall];

}

 
  $result['data'] = $resAll;
  $result['status'] = true;
  $result['message'] = 'Get Order Info Successfully';  
  echo json_encode($result);
}
catch( Exception $e )
{
  	$result['data'] = null;
	$result['status'] = false;
	$result['message'] = $e->getMessage();
	echo json_encode($result);
}

}


elseif ($request['action'] == "orderdetailinfo") {

$orderid= $request['increment_id'];
try{
    $result1 = $client->salesOrderInfo($session, $orderid);
	//print_r($result1);
	$allitems=$result1->shipping_address;
	
	
  $allitems=$result1->shipping_address;
  $allitems1=$result1->billing_address;
  $allitems2=$result1->items;
  $allitems3=$result1->payment;
  $allitems4=$result1->status_history;

  $resc=array();
  $rescallinformation=array();
  foreach($allitems2 as $allitemsimages) {
	   $productimgid=$allitemsimages->product_id;
	  $resc['product_id']=$productimgid;
	  $resc['sku']=$allitemsimages->sku;
	  $resc['qty_ordered']=$allitemsimages->qty_ordered;
	  $resc['name']=$allitemsimages->name;
	  $resc['price']=$allitemsimages->price;
	if(!empty($productimgid)){  
	  $obj = Mage::getModel('catalog/product');
$_product = $obj->load($productimgid);

	
$resc['image_url']=$_product->getImageUrl();
}
 //print_r($resc);
 $rescallinformation[]=$resc;
 }

  $result['data'] = $rescallinformation;
  $result['shipping_address'] = $allitems;
  $result['billing_address'] = $allitems1;
   $result['payment'] =  $allitems3;
   $result['status_history'] =  $allitems4;
  $result['status'] = true;
  $result['message'] = 'Get Order Info Details Successfully';  
  echo json_encode($result);
}
catch( Exception $e )
{
  	$result['data'] = null;
	$result['status'] = false;
	$result['message'] = $e->getMessage();
	echo json_encode($result);
}

}

elseif ($request['action'] == "shoppingcartremove") {
   
    $order_id=$request['order_id'];
	
	$quote_id=$request['quote_id'];
	$cartarray=$request['cart'];

			
try{
	
	$cartremove = $client->shoppingCartProductRemove($session, $quote_id,$cartarray);
	if($order_id!=''){
  	$result5['data'] = $cartremove;
	$result5['status'] = true;
	$result5['message'] = 'Order placed successfully.';	 
	echo json_encode($result5);
	} else {
		$result5['data'] = null;
			$result5['status'] = false;
			$result5['message'] = 'Some error occured please contact admin.';
			echo json_encode($result5);
	}
		
	
	                        }catch( Exception $e ) {
			$result5['data'] = null;
			$result5['status'] = false;
			$result5['message'] = 'Some error occured please contact admin.';
			echo json_encode($result5);
		
	}
}



elseif ($request['action'] == "customeraddressupdated") {
$customeradrres_id= $request['customeradrres_id'];

	$firstname= $request['firstname'];
	$lastname= $request['lastname'];
	$street_line_1= $request['street_line_1'];
	$street_line_2= $request['street_line_2'];
	$city= $request['city'];
	$region= $request['region'];
	$region_id= $request['region_id'];
	$postcode= $request['postcode'];
	$country_id= $request['country_id'];
	$telephone= $request['telephone'];
	$is_default_billing= $request['is_default_billing'];
	$is_default_shipping= $request['is_default_shipping'];
            try 
            {
				
				$result2 = $client->customerAddressUpdate($session, $customeradrres_id, array('firstname' => $firstname, 'lastname' => $lastname, 'street' => array($street_line_1, $street_line_2), 'city' => $city, 'country_id' => $country_id, 'region' => $region, 'region_id' => $region_id, 'postcode' => $postcode, 'telephone' => $telephone, 
				'is_default_billing' => FALSE, 'is_default_shipping' => FALSE));

//var_dump ($result2);
 
                        $result['data']=$result2;
                       $result['status'] = true;
                       $result['message'] = 'Address updated successfully.';	 
                       echo json_encode($result);
              
	
            } 


catch( Exception $e )
{
  	$result['data'] = null;
	$result['status'] = false;
	$result['message'] = $e->getMessage();
	echo json_encode($result);
}

}

elseif ($request['action'] == "customershppingaddress") {
$customer_id= $request['customer_id'];
            try 
            {
				$resource = Mage::getSingleton('core/resource');
      $writeConnection = $resource->getConnection('core_write');
	echo $sql1= "SELECT * FROM sales_flat_quote_item where cart_user_id='$customer_id'  ORDER BY item_id DESC LIMIT 1";
	$rowssku1       = $writeConnection->fetchAll($sql1);
	print_r($rowssku1);
	$qutoe1=array();
  foreach($rowssku1 as $fetchrowssku1){
      echo $qutoe1[]=$fetchrowssku1['quote_id'];

    
   }
   
   echo $qutoe1;
   exit();
	
				$result = $client->shoppingCartCustomerAddresses($session, 150, array(array(
'mode' => 'billing',
'firstname' => 'm1',
'lastname' => 'k1',
'street' => 'street address',
'city' => 'city',
'region' => 'region',
'postcode' => 'postcode',
'country_id' => 'US',
'telephone' => '123456789',
'is_default_billing' => 1
)));   
var_dump($result);
exit();

 
                        $result['data']=$result2;
                       $result['status'] = true;
                       $result['message'] = 'Address updated successfully.';	 
                       echo json_encode($result);
              
	
            } 


catch( Exception $e )
{
  	$result['data'] = null;
	$result['status'] = false;
	$result['message'] = $e->getMessage();
	echo json_encode($result);
}

}


elseif ($request['action'] == "customershppinginformation") 
{
				  $customerId  = $request['customer_id'];		
				  
	        try {
				  $customer  = Mage::getModel('customer/customer')->load($customerId);
				  $defaultShippingId = $customer->getDefaultShipping();
				  $address = Mage::getModel('customer/address')->load($defaultShippingId);
                  $addressinfo=$address->getData();
				 $addressinfoentity=$addressinfo['entity_id'];
				 
				  
				  
		        if(!empty($addressinfoentity)){
				  $result['data']=$addressinfo;
				  $result['status'] = true;
				  $result['message'] = 'Address avilable.';				  	 
				  echo json_encode($result);
				
			    }else {
				  $result['data']= null;
				  $result['status'] = true;
				  $result['message'] = 'Address not avilable.';				  	 
				  echo json_encode($result);					  			   
	               }
				}
		    catch( Exception $e )
                {
					$result['data'] = null;
					$result['status'] = false;
					$result['message'] = $e->getMessage();
					echo json_encode($result);
                }		
}

elseif ($request['action'] == "customerBillinginformation") 
{
				  $customerId  = $request['customer_id'];		
				  
	        try {
				  $customer  = Mage::getModel('customer/customer')->load($customerId);
				  $defaultShippingId = $customer->getDefaultBilling();
				  $address = Mage::getModel('customer/address')->load($defaultShippingId);
                  $addressinfo=$address->getData();
				 $addressinfoentity=$addressinfo['entity_id'];
				 
				  
				  
		        if(!empty($addressinfoentity)){
				  $result['data']=$addressinfo;
				  $result['status'] = true;
				  $result['message'] = 'Address avilable.';				  	 
				  echo json_encode($result);
				
			    }else {
				  $result['data']=null;
				  $result['status'] = true;
				  $result['message'] = 'Address not avilable.';				  	 
				  echo json_encode($result);					  			   
	               }
				}
		    catch( Exception $e )
                {
					$result['data'] = null;
					$result['status'] = false;
					$result['message'] = $e->getMessage();
					echo json_encode($result);
                }		
}


elseif ($request['action'] == "customershppingaddress1") 
{
    $customerId  = $request['customer_id'];		
	$customer  = Mage::getModel('customer/customer')->load($customerId);
	 $defaultShippingId = $customer->getDefaultShipping();
	 $defaultBillingId = $customer->getDefaultBilling();
	
	$billingaddress = Mage::getModel('customer/address')->load($defaultBillingId);
	$billingaddress ->setFirstname("Indranil")->setLastname("Maity")->save();
	
	$shippingaddress = Mage::getModel('customer/address')->load($defaultShippingId);
	$shippingaddress ->setFirstname("Aneel")->setLastname("Kumar")->save();
	
	echo $defaultShippingId.'-'.$defaultBillingId;

}

elseif ($request['action'] == "customeralladdresslist") 
{
    $customerId  = $request['customer_id'];	
    $result = $client->customerAddressList($session, $customerId);
var_dump($result);	
	
}

elseif ($request['action'] == "addnewaddress") 
{
	$customerId  = $request['customer_id'];
    $firstname= $request['firstname'];
	$lastname= $request['lastname'];
	$street_line_1= $request['street_line_1'];
	$street_line_2= $request['street_line_2'];
	$city= $request['city'];
	$region= $request['region'];
	$region_id= $request['region_id'];
	$postcode= $request['postcode'];
	$country_id= $request['country_id'];
	$telephone= $request['telephone'];	
   

   try {
	    $_custom_address = array('firstname' => $firstname, 'lastname' => $lastname, 'street' => array($street_line_1, $street_line_2), 'city' => $city,'region' => $region, 'region_id' => $region_id, 'postcode' => $postcode,'country_id' => $country_id,'telephone' => $telephone);
		$customAddress = Mage::getModel('customer/address');
		//$customAddress = new Mage_Customer_Model_Address();
		$customAddress->setData($_custom_address)
					->setCustomerId($customerId);
				//->setIsDefaultBilling('1')
				//->setIsDefaultShipping('1');
		$result2=$customAddress->save();

		                $result['data']=$result2;
                        $result['status'] = true;
                        $result['message'] = 'Address saved successfully.';	 
                        echo json_encode($result);
		}
		catch( Exception $e )
                {
					$result['data'] = [];
					$result['status'] = false;
					$result['message'] = $e->getMessage();
					echo json_encode($result);
                }	
	
	
}

elseif ($request['action'] == "customerAddressDelete") {
	
	$customeraddress_id= $request['customeraddress_id'];

            try 
            {
            $result2 = $client->customerAddressDelete($session, $customeraddress_id);

 
                        $result['data']=$result2;
                        $result['status'] = true;
                        $result['message'] = 'Address deleted successfully.';	 
                        echo json_encode($result);
              
	
            } 
            catch( Exception $e )
            {
          $result['data'] = [];
		$result['status'] = false;
		$result['message'] = $e->getMessage();
		echo json_encode($result);
            }
	

}

elseif ($request['action'] == "editBillingAddress") 
{
	$customerId  = $request['customer_id'];
    $firstname= $request['firstname'];
	$lastname= $request['lastname'];
	$street_line_1= $request['street_line_1'];
	$street_line_2= $request['street_line_2'];
	$city= $request['city'];
	$region= $request['region'];
	$region_id= $request['region_id'];
	$postcode= $request['postcode'];
	$country_id= $request['country_id'];
	$telephone= $request['telephone'];	
   

   try {
	    $_custom_address = array('firstname' => $firstname, 'lastname' => $lastname, 'street' => array($street_line_1, $street_line_2), 'city' => $city,'region' => $region, 'region_id' => $region_id, 'postcode' => $postcode,'country_id' => $country_id,'telephone' => $telephone);
		$customAddress = Mage::getModel('customer/address');
		//$customAddress = new Mage_Customer_Model_Address();
		$customAddress->setData($_custom_address)
					->setCustomerId($customerId)
					->setSaveInAddressBook('0')
				->setIsDefaultBilling('1');
				//->setIsDefaultShipping('1');
		$result2=$customAddress->save();

		                $result['data']=$result2;
                        $result['status'] = true;
                        $result['message'] = 'Billing address changed successfully.';	 
                        echo json_encode($result);
		}
		catch( Exception $e )
                {
					$result['data'] = [];
					$result['status'] = false;
					$result['message'] = $e->getMessage();
					echo json_encode($result);
                }	
	
}
elseif ($request['action'] == "editShippingAddress") 
{
	$customerId  = $request['customer_id'];
    $firstname= $request['firstname'];
	$lastname= $request['lastname'];
	$street_line_1= $request['street_line_1'];
	$street_line_2= $request['street_line_2'];
	$city= $request['city'];
	$region= $request['region'];
	$region_id= $request['region_id'];
	$postcode= $request['postcode'];
	$country_id= $request['country_id'];
	$telephone= $request['telephone'];	
   

   try {
	    $_custom_address = array('firstname' => $firstname, 'lastname' => $lastname, 'street' => array($street_line_1, $street_line_2), 'city' => $city,'region' => $region, 'region_id' => $region_id, 'postcode' => $postcode,'country_id' => $country_id,'telephone' => $telephone);
		$customAddress = Mage::getModel('customer/address');
		//$customAddress = new Mage_Customer_Model_Address();
		$customAddress->setData($_custom_address)
					->setCustomerId($customerId)
					->setSaveInAddressBook('0')
					->setIsDefaultShipping('1');
				
				//->setIsDefaultShipping('1');
		$result2=$customAddress->save();

		                $result['data']=$result2;
                        $result['status'] = true;
                        $result['message'] = 'Shipping address changed successfully.';	 
                        echo json_encode($result);
		}
		catch( Exception $e )
                {
					$result['data'] = [];
					$result['status'] = false;
					$result['message'] = $e->getMessage();
					echo json_encode($result);
                }	
	
}

elseif ($request['action'] == "editBillingandShippingAddress") 
{
	$customerId  = $request['customer_id'];
    $firstname= $request['firstname'];
	$lastname= $request['lastname'];
	$street_line_1= $request['street_line_1'];
	$street_line_2= $request['street_line_2'];
	$city= $request['city'];
	$region= $request['region'];
	$region_id= $request['region_id'];
	$postcode= $request['postcode'];
	$country_id= $request['country_id'];
	$telephone= $request['telephone'];	
   

   try {
	    $_custom_address = array('firstname' => $firstname, 'lastname' => $lastname, 'street' => array($street_line_1, $street_line_2), 'city' => $city,'region' => $region, 'region_id' => $region_id, 'postcode' => $postcode,'country_id' => $country_id,'telephone' => $telephone);
		$customAddress = Mage::getModel('customer/address');
		//$customAddress = new Mage_Customer_Model_Address();
		$customAddress->setData($_custom_address)
					->setCustomerId($customerId)
					->setSaveInAddressBook('0')
					->setIsDefaultBilling('1')
					->setIsDefaultShipping('1');
				
				//->setIsDefaultShipping('1');
		$result2=$customAddress->save();

		                $result['data']=$result2;
                        $result['status'] = true;
                        $result['message'] = 'Billing and Shipping address changed successfully.';	 
                        echo json_encode($result);
		}
		catch( Exception $e )
                {
					$result['data'] = [];
					$result['status'] = false;
					$result['message'] = $e->getMessage();
					echo json_encode($result);
                }	
	
}

?>
