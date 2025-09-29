<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div class='slack'>
    <Field
        uicontrol="text"
        name="channelID"
        :title="translate('Slack_ChannelId')"
        class="slack"
        :model-value="modelValue"
        :disabled="!isSlackOauthTokenAdded"
        @update:model-value="$emit('update:modelValue', $event)"
    >
      <template v-slot:inline-help>
        <div id="slackChannelIDHelp" class="inline-help-node">
          <span
              v-if="!isSlackOauthTokenAdded"
              style="margin-right:3.5px"
              v-html="$sanitize(getSlackOAuthTokenNotAddedHelpText)"
          >
                </span>
          <span
              v-else
              v-html="$sanitize(getSlackChannelHelpText)"
          >
                </span>
        </div>
      </template>
    </Field>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { MatomoUrl, translate, externalLink } from 'CoreHome';
import { Field } from 'CorePluginsAdmin';

export default defineComponent({
  props: {
    modelValue: String,
    isSlackOauthTokenAdded: {
      type: Boolean,
      default: false,
    },
    withIntroduction: Boolean,
  },
  emits: ['update:modelValue'],
  components: {
    Field,
  },
  methods: {
    linkTo(params: QueryParameters) {
      return `?${MatomoUrl.stringify({
        ...MatomoUrl.urlParsed.value,
        ...params,
      })}`;
    },
  },
  computed: {
    getSlackOAuthTokenNotAddedHelpText() {
      const link = this.linkTo({ module: 'CoreAdminHome', action: 'generalSettings', updated: null });
      return translate(
        'Slack_NoOauthTokenAdded',
        `<a href="${link}#/Slack" rel="noreferrer noopener" target="_blank">`,
        '</a>',
      );
    },
    getSlackChannelHelpText() {
      return translate(
        'Slack_SlackEnterYourSlackChannelIdHelpText',
        externalLink('https://matomo.org/faq/reports/how-to-integrate-slack-for-scheduled-reports/#step-2-set-up-slack-in-matomo'),
        '</a>',
      );
    },
  },
});
</script>
