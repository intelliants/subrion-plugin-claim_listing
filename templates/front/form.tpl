<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	<h4 class="modal-title">{lang key='claim_listing'}</h4>
</div>
<div class="modal-body">
	<form action="{$claimListing.formUrl}" method="post" class="ia-form">
		<input type="hidden" name="id" value="{$claimListing.id}">
		<input type="hidden" name="item" value="{$claimListing.item}">

		<div class="form-group">
			<label>{lang key='choose_option'}</label>
			<select name="type" class="form-control">
				<option value="manual" selected>{lang key='claim_option_manual'}</option>
				{if $claimListing.options.ftp}
				<option value="ftp">{lang key='claim_option_ftp'}</option>
				{/if}
				{if $claimListing.options.email}
				<option value="email">{lang key='claim_option_email'}</option>
				{/if}
			</select>
			<p id="js-cl-option-descriptions">
				<span data-type="manual">{lang key='claim_option_manual_description'}</span>
				<span class="hide" data-type="ftp">{lang key='claim_option_ftp_description'}</span>
				<span class="hide" data-type="email">{lang key='claim_option_email_description'}</span>
			</p>
		</div>

		<hr>

		<div class="js-options js-option-manual">
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label class="control-label">{lang key='your_name'}</label>
						<input type="text" name="name" class="form-control">
						<p class="help-block hide">{lang key='empty_field_notice'}</p>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label>{lang key='your_email'}</label>
						<input type="text" name="email" class="form-control">
						<p class="help-block hide">{lang key='empty_field_notice'}</p>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label>{lang key='telephone_number'}</label>
						<input type="text" name="phone" class="form-control">
						<p class="help-block hide">{lang key='empty_field_notice'}</p>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label>{lang key='you_are'}</label>
						<select name="job_title" class="form-control">
							<option>{lang key='owner'}</option>
							<option>{lang key='director'}</option>
							<option>{lang key='partner'}</option>
							<option>{lang key='manager'}</option>
							<option>{lang key='staff_member'}</option>
						</select>
					</div>
				</div>
			</div>

			<div class="m-t text-right">
				<button type="submit" class="btn btn-inverse" disabled>{lang key='claim'}</button>
			</div>
		</div>

		{if $claimListing.options.ftp}
		<div class="js-options js-option-ftp hide">
			<div class="form-group">
				<input type="text" class="form-control col-md-7 js-check-filename" value="{$claimListing.filename}" readonly>
				<p class="help-block">{lang key='ftp_claim_instructions'}</p>
			</div>
			<div class="form-group">
				{lang key='url_to_be_checked'}
				<span class="label label-info"><span class="js-check-url">{$claimListing.url}</span>{$claimListing.filename}</span>
			</div>
			<div class="alert js-url-check-box hide"></div>

			<div class="m-t text-right">
				<button type="button" class="btn btn-default js-cmd-check-url" data-loading-text="{lang key='checking'}">
					<i class="icon-refresh"></i> {lang key='check'}</button>
				<button type="submit" class="btn btn-default btn-inverse pull-right hide">{lang key='claim'}</button>
			</div>
		</div>
		{/if}

		{if $claimListing.options.email}
		<div class="js-options js-option-email hide">
			<div class="form-group">
				{if empty($claimListing.email)}
					<div class="alert alert-info">
						{lang key='unable_to_use_email_claim'}
					</div>
				{else}
					{lang key='confirmation_link_email' email=$claimListing.email}
				{/if}
			</div>

			{if $claimListing.email}
				<div class="form-group">
					<label><input type="checkbox" class="js-confirm-checkbox"> {lang key='claim_email_submission_confirmation'}</label>
				</div>
			{/if}

			<div class="m-t text-right">
				{if empty($claimListing.email)}
					<button class="btn btn-default" data-dismiss="modal">{lang key='cancel'}</button>
				{else}
					<button type="submit" class="btn btn-inverse" disabled>{lang key='send_confirmation_email'}</button>
				{/if}
			</div>
		</div>
		{/if}
	</form>
</div>
<script type="text/javascript" src="plugins/claim_listing/js/form.js"></script>