<?php if(!class_exists('Rain\Tpl')){exit;}?><!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Lista de Usuários
  </h1>
</section>

<!-- Main content -->
<section class="content">

  <div class="row">
  	<div class="col-md-12">
  		<div class="box box-primary">
        <div class="box-header with-border">
          <h3 class="box-title">Editar Usuário</h3>
        </div>
        <!-- /.box-header -->
        <!-- form start -->
        <form role="form" action="/admin/users/<?php echo htmlspecialchars( $user["id_user"], ENT_COMPAT, 'UTF-8', FALSE ); ?>" method="post">
          <div class="box-body">
            <div class="form-group">
              <label for="des_person">Nome</label>
              <input type="text" class="form-control" id="des_person" name="des_person" placeholder="Digite o nome" value="<?php echo htmlspecialchars( $user["des_person"], ENT_COMPAT, 'UTF-8', FALSE ); ?>">
            </div>
            <div class="form-group">
              <label for="des_login">Login</label>
              <input type="text" class="form-control" id="des_login" name="des_login" placeholder="Digite o login"  value="<?php echo htmlspecialchars( $user["des_login"], ENT_COMPAT, 'UTF-8', FALSE ); ?>">
            </div>
            <div class="form-group">
              <label for="nr_phone">Telefone</label>
              <input type="tel" class="form-control" id="nr_phone" name="nr_phone" placeholder="Digite o telefone"  value="<?php echo htmlspecialchars( $user["nr_phone"], ENT_COMPAT, 'UTF-8', FALSE ); ?>">
            </div>
            <div class="form-group">
              <label for="des_email">E-mail</label>
              <input type="email" class="form-control" id="des_email" name="des_email" placeholder="Digite o e-mail" value="<?php echo htmlspecialchars( $user["des_email"], ENT_COMPAT, 'UTF-8', FALSE ); ?>">
            </div>
            <div class="checkbox">
              <label>
                <input type="checkbox" name="is_admin" value="1" <?php if( $user["is_admin"] == 1 ){ ?>checked<?php } ?>> Acesso de Administrador
              </label>
            </div>
          </div>
          <!-- /.box-body -->
          <div class="box-footer">
            <button type="submit" class="btn btn-primary">Salvar</button>
          </div>
        </form>
      </div>
  	</div>
  </div>

</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->