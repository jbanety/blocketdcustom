{**
 * @package     blocketdcustom
 *
 * @version     0.0.1
 * @copyright   Copyright (C) 2014 Jean-Baptiste Alleaume. Tous droits réservés.
 * @license     http://alleau.me/LICENSE
 * @author      Jean-Baptiste Alleaume http://alleau.me
 *}
{extends file="helpers/form/form.tpl"}

{block name="input"}

    {if $input.type == 'customs'}

		{assign var=customs value=$input.values}

		<table cellspacing="0" cellpadding="0" class="table" style="min-width:40em;">
			<tr>
				<th>{l s='Name' mod='blocketdcustom'}</th>
				<th>{l s='Status' mod='blocketdcustom'}</th>
				<th>{l s='Ordering' mod='blocketdcustom'}</th>
				<th>{l s='Access' mod='blocketdcustom'}</th>
				<th>{l s='Hook' mod='blocketdcustom'}</th>
				<th>{l s='ID' mod='blocketdcustom'}</th>
				<th>{l s='Actions' mod='blocketdcustom'}</th>
			</tr>

			{foreach $customs as $key => $custom}

				<tr {if $custom@iteration % 2}class="alt_row"{/if}>
					<td>
						<strong><a href="{$current}&token={$token}&editCustom&id_custom={(int)$custom.id}" title="{l s='Edit' mod='blocketdcustom'}">{$custom.title}</a></strong>
					</td>
					<td align="center">
						<img src="{$smarty.const._PS_ADMIN_IMG_}{if $custom.published}enabled.gif{else}disabled.gif{/if}" alt="{if $custom.published}{l s='Enabled'}{else}{l s='Disabled'}{/if}" title="{if $custom.published}{l s='Enabled'}{else}{l s='Disabled'}{/if}" />
					</td>
					<td>
						{if isset($custom.parent_id) && isset($input.ordering[$custom.parent_id][$orderkey - 1])}
						<a href="{$current}&token={$token}&orderUp&id_custom={(int)$custom.id}">
							<img src="{$smarty.const._PS_ADMIN_IMG_}{if $order_way == 'ASC'}down{else}up{/if}.gif" alt="{l s='Down'}" title="{l s='Down'}" />
						</a>
						{/if}
						{if isset($custom.parent_id) && isset($input.ordering[$custom.parent_id][$orderkey + 1])}
							<a href="{$current}&token={$token}&orderDown&id_custom={(int)$custom.id}">
							<img src="{$smarty.const._PS_ADMIN_IMG_}{if $order_way == 'ASC'}up{else}down{/if}.gif" alt="{l s='Up'}" title="{l s='Up'}" />
						</a>
						{/if}
					</td>
					<td>
						{if $custom.access == 0}
							{l s='Public'}
						{elseif $custom.access == 1}
							{l s='Guests'}
						{elseif $custom.access == 2}
							{l s='Customers'}
						{/if}
					</td>
					<td>
						{$custom.hook}
					</td>
					<td>
						{$custom.id}
					</td>
					<td>
						<a href="{$current}&token={$token}&editCustom&id_custom={(int)$custom.id}" title="{l s='Edit' mod='blocketdcustom'}"><img src="{$smarty.const._PS_ADMIN_IMG_}edit.gif" alt="" /></a>
						<a href="{$current}&token={$token}&deleteCustom&id_custom={(int)$custom.id}" title="{l s='Delete' mod='blocketdcustom'}"><img src="{$smarty.const._PS_ADMIN_IMG_}delete.gif" alt="" /></a>
					</td>
				</tr>

			{/foreach}

		</table>

	{elseif $input.type == 'exceptions'}

		<input type="text" name="{$input.name}" size="40" value="{implode(',', $fields_value[$input.name])}" id="em_text">{if isset($input.required) && $input.required} <sup>*</sup>{/if}<br>
		<select id="em_list">';
		{foreach $input.controllers as $k => $v}
			<option value="{$k}">{$k}</option>
		{/foreach}
		</select> <input type="button" class="button" value="{l s='Add'}">
		<input type="button" class="button" value="{l s='Remove'}">

		{literal}
		<script>
			jQuery(document).ready(function() {

			});
		</script>
		{/literal}

	{elseif $input.type == 'hooks'}

		<select name="{$input.name}" id="{$input.name}">';
			<option value="">--</option>
		{foreach $input.hooks as $hook}
			<option value="{$hook}"{if $hook == $fields_value[$input.name]} selected{/if}>{$hook}</option>
		{/foreach}
		</select>
		{if isset($input.required) && $input.required} <sup>*</sup>{/if}
	{else}
		{$smarty.block.parent}
    {/if}

{/block}
