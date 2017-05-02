

                                               <div class="col-xs-11">
   
   <div class="box">
                <div class="box-header">
                  <h6 class="box-title text-green" ><?php echo $message;?></h6>
                </div><!-- /.box-header -->
                <div class="box-body">
                  <table id="example1" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Options</th>
                      </tr>
                    </thead>
                    <tbody>
              <?php foreach ($insurers  as $insurer):?>          
                     	<tr>
            <td><?php echo htmlspecialchars($insurer->name ,ENT_QUOTES,'UTF-8');?></td>
            <td><?php echo htmlspecialchars($insurer->contact_person ,ENT_QUOTES,'UTF-8');?></td>
            <td><?php echo htmlspecialchars($insurer->phone,ENT_QUOTES,'UTF-8');?></td>
            <td><?php echo htmlspecialchars($insurer->address,ENT_QUOTES,'UTF-8');?></td>
            <td><?php echo htmlspecialchars($insurer->email,ENT_QUOTES,'UTF-8');?></td>

            <td><?php echo ($insurer->status) ? '<span class="label label-success">'.  status_by_id($insurer->status).'</span>' : '<span class="label label-danger">'.status_by_id($insurer->status).'</span>';?></td>
	    <td><?php echo anchor("insurer/edit/".$insurer->id_insurer, '<i class="fa fa-edit"></i>','title="Edit"') ;?> | <?php echo anchor("insurer/delete/".$insurer->id_insurer, '<i class="fa fa-trash"></i>',array('title'=>"Delete",'onclick' => "return confirm('Do you want delete this record')")) ;?></td>
		</tr>
                        
                     <?php endforeach;?>
                    </tbody>
                    <tfoot>
                      <tr>
                        <th>Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Options</th>
                      </tr>
                    </tfoot>
                  </table>
                    <p><?php echo anchor('insurer/add', '<i class="fa fa-plus-circle"></i> Add Insurer')?> </p>

                </div><!-- /.box-body -->
                      </div><!-- /.box -->
   
   
   
   </div>