<form method="post" enctype="multipart/form-data" class="sap-form form-horizontal">
	{preventCsrf}

	<div class="wrap-list">
		<div class="wrap-group">
			<div class="wrap-group-heading">
				<h4>{lang key='general'}</h4>
			</div>

			<div class="row">
				<label class="col col-lg-2 control-label">{lang key='initiator'}</label>
				<div class="col col-lg-4">
					<a href="members/edit/{$initiator.id}/" target="_blank">{$initiator.fullname|escape:'html'}</a> <small class="muted">/{$initiator.username}</small>
				</div>
			</div>

			<div class="row">
				<label class="col col-lg-2 control-label">{lang key='status'}</label>
				<div class="col col-lg-4">
					<span class="label label-{if 'approved' == $item.status}success{elseif 'canceled' == $item.status}default{else}info{/if}">{$item.status}</span>
				</div>
			</div>

			<div class="row">
				<label class="col col-lg-2 control-label">{lang key='date'}</label>
				<div class="col col-lg-4">
					{$item.date} <span class="label">{$item.ip|long2ip}</span>
				</div>
			</div>

			<div class="row">
				<label class="col col-lg-2 control-label">{lang key='type'}</label>
				<div class="col col-lg-4">
					{lang key="claim_approval_{$item.type}"}
				</div>
			</div>
		</div>

		{if $item.notes}
		<div class="wrap-group">
			<div class="wrap-group-heading">
				<h4>{lang key='notes'}</h4>
			</div>

			<div class="row">
				{$item.notes|escape}
			</div>
		</div>
		{/if}

		{if 'manual' == $item.type}
		<div class="wrap-group">
			<div class="wrap-group-heading">
				<h4>{lang key='visitor_info'}</h4>
			</div>

			<div class="row">
				<label class="col col-lg-2 control-label">{lang key='name'}</label>
				<div class="col col-lg-4">
					{$item.name|escape}
				</div>
			</div>

			<div class="row">
				<label class="col col-lg-2 control-label">{lang key='email'}</label>
				<div class="col col-lg-4">
					{$item.email|escape}
				</div>
			</div>

			<div class="row">
				<label class="col col-lg-2 control-label">{lang key='phone'}</label>
				<div class="col col-lg-4">
					{$item.phone|escape}
				</div>
			</div>

			<div class="row">
				<label class="col col-lg-2 control-label">{lang key='job_title'}</label>
				<div class="col col-lg-4">
					{$item.job_title|escape}
				</div>
			</div>
		</div>
		{/if}

		<div class="form-actions">
			<a href="{$smarty.const._ADMIN_URL}claims/" class="btn btn-default"><i class="i-chevron-left"></i> {lang key='back'}</a>

			<form method="post">
				<input type="hidden" name="id" value="{$item.id}">
				<button type="submit" name="data-approve" class="btn btn-primary js-cmd-approve pull-right"{if 'approved' == $item.status} disabled{/if}>
					<i class="i-checkmark"></i> {lang key='approve_this_claim'}</button>
			</form>
		</div>
	</div>
</form>
{ia_add_js}
$(function()
{
	$('.js-cmd-approve').on('click', function(e)
	{
		confirm(_t('do_you_want_to_approve_claim')) || e.preventDefault();
	});
});
{/ia_add_js}