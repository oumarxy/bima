

                                               <div class="col-xs-12">
   
   <div class="box">
                <div class="box-header">
                  <h6 class="box-title text-green" ><?php echo $message;?></h6>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Registered on</th>
                        <th>Updated on</th>
                        <th>Status</th>
                        <th>Options</th>
                      </tr>
                    </thead>
                    <tbody>
              <?php foreach ($customers  as $customer):?>          
                     	<tr>
            <td><?php echo htmlspecialchars($customer->name,ENT_QUOTES,'UTF-8');?></td>
            <td><?php echo htmlspecialchars($customer->address,ENT_QUOTES,'UTF-8');?></td>
            <td><?php echo htmlspecialchars($customer->phone,ENT_QUOTES,'UTF-8');?></td>
            <td><?php echo format_date('',$customer->registered_on);?></td>
            <td><?php echo ($customer->updated_on>0) ? format_date('',$customer->updated_on) : '';?></td>
       

            <td><?php echo ($customer->status) ? '<span class="label label-success">'.  status_by_id($customer->status).'</span>' : '<span class="label label-danger">'.status_by_id($customer->status).'</span>';?></td>
	   <td> <?php echo anchor("customer/edit/".$customer->id_customer, '<i class="fa fa-edit"></i>','title="Edit"') ;?>| <?php echo anchor("customer/delete/" .$customer->id_customer, '<i class="fa fa-trash"></i>',  array('title'=>"Delete",'onclick' => "return confirm('This will delete property, insurance and payment records associated to this customer. Do you want to delete?')")); ?> | <?php echo anchor("property/index/".$customer->id_customer, '<i class="fa fa-sticky-note-o"></i>','title="Property"') ;?></td>
           
                        </tr>
                        
                     <?php endforeach;?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Registered on</th>
                        <th>Updated on</th>
                        <th>Status</th>
                        <th>Options</th>
                         </tr>
                    </tfoot>
                  </table>
                     <p><?php echo anchor('customer/register', '<i class="fa fa-plus-circle"></i> Register Customer')?> | <?php echo anchor('customer/import', '<i class="fa fa-upload"></i> Import Bulk Customers')?></p>
                     </div><!-- /.box-body -->
                      </div><!-- /.box -->
   
   
   
   </div>