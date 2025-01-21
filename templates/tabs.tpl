{* <tab id="submissionsInReview" label="{translate key='common.queue.short.submissionsInReview'}">
</tab> *}

<tab id="customSubmissions" label="{translate key="common.queue.short.submissionsInReview"}" :badge="components.customSubmissions.itemsMax">
    <submissions-list-panel
        v-bind="components.customSubmissions"
        @set="set"
    >

    <template v-slot:item="{ldelim}item{rdelim}">
        {* apiURL = {ldelim}{ldelim}components.customSubmissions.apiUrl{rdelim}{rdelim}<br/> *}
        <custom-submissions-list-item
            :key="item.id"
            :item="item"
            :components="components"
            :apiUrl="components.customSubmissions.apiUrl"
            :infoUrl="components.customSubmissions.infoUrl"
            :assignParticipantUrl="components.customSubmissions.assignParticipantUrl"
            @addFilter="components.customSubmissions.addFilter"
            />
        </template>
    </submission-list-panel>
    
</tab>