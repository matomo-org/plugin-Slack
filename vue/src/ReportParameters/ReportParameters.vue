<!--
  Matomo - free/libre analytics platform

  @link    https://matomo.org
  @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
-->

<template>
  <div v-if="report && report.type === 'slack'">
    <SelectSlackChannel
        :is-slack-oauth-token-added="isSlackOauthTokenAdded"
        :with-introduction="true"
        :model-value="report?.slackChannelID"
        @update:model-value="$emit('change', 'slackChannelID', $event)"
    />
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Report } from 'ScheduledReports';
import SelectSlackChannel from '../SelectSlackChannel/SelectSlackChannel.vue';

const REPORT_TYPE = 'slack';

export default defineComponent({
  props: {
    report: {
      type: Object,
      required: true,
    },
    isSlackOauthTokenAdded: {
      type: Boolean,
      default: false,
    },
  },
  components: {
    SelectSlackChannel,
  },
  emits: ['change'],
  created() {
    const {
      resetReportParametersFunctions,
      updateReportParametersFunctions,
      getReportParametersFunctions,
    } = window;

    if (!resetReportParametersFunctions[REPORT_TYPE]) {
      resetReportParametersFunctions[REPORT_TYPE] = (report: Report) => {
        report.slackChannelID = '';
      };
    }

    if (!updateReportParametersFunctions[REPORT_TYPE]) {
      updateReportParametersFunctions[REPORT_TYPE] = (report: Report) => {
        if (!report?.parameters) {
          return;
        }

        if (report.parameters && report.parameters.slackChannelID) {
          report.slackChannelID = report.parameters.slackChannelID;
        }
      };
    }

    if (!getReportParametersFunctions[REPORT_TYPE]) {
      getReportParametersFunctions[REPORT_TYPE] = (report: Report) => ({
        slackChannelID: report.slackChannelID,
      });
    }
  },
});
</script>
