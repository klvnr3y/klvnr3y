import React from "react";
import {
	Route,
	Switch,
	// Route
} from "react-router-dom";
import {
	faBullhorn,
	faPaperPlane,
	faHome,
	faUsers,
	faUserCog,
} from "@fortawesome/pro-regular-svg-icons";
import { faBell, faUserEdit } from "@fortawesome/pro-solid-svg-icons";

/** template */
import PrivateRoute from "../PrivateRoute";

import Error404 from "../../views/errors/Error404";
import Error500 from "../../views/errors/Error500";

import PageDashboard from "../../views/private/PageAdmin/PageDashboard/PageDashboard";
import PageSubscribersCurrent from "../../views/private/PageAdmin/PageSubscribers/PageSubscribersCurrent/PageSubscribersCurrent";
import PageSubscribersCurrentEdit from "../../views/private/PageAdmin/PageSubscribers/PageSubscribersCurrent/PageSubscribersCurrentEdit";
import PageSubscribersDeactivated from "../../views/private/PageAdmin/PageSubscribers/PageSubscribersDeactivated/PageSubscribersDeactivated";
import PageNotifications from "../../views/private/PageAdmin/PageNotifications/PageNotifications";
import PageEmailTemplates from "../../views/private/PageAdmin/PageEmailTemplates/PageEmailTemplates";
import PageAccountType from "../../views/private/PageAdmin/PageAccountType/PageAccountType";
import ViewAs from "../../views/private/ViewAs/ViewAs";

export default function RouteAdmin() {
	// console.log("RouteAdmin");
	return (
		<Switch>
			<PrivateRoute
				exact
				path="/dashboard"
				title="Dashboard"
				subtitle="ADMIN"
				component={PageDashboard}
				pageHeaderIcon={faHome}
				breadcrumb={[
					{
						name: "Dashboard",
						link: "/dashboard",
					},
				]}
			/>

			{/* Subscribers */}
			<PrivateRoute
				exact
				path="/subscribers/current"
				title="Subscribers"
				subtitle="CURRENT"
				component={PageSubscribersCurrent}
				pageHeaderIcon={faUsers}
				breadcrumb={[
					{
						name: "Dashboard",
						link: "/dashboard",
					},
					{
						name: "Subscribers",
						link: "/subscribers/current",
					},
					{
						name: "Current",
						link: "/subscribers/current",
					},
				]}
			/>

			<PrivateRoute
				exact
				path="/subscribers/current/edit"
				title="Subscriber"
				subtitle="EDIT CURRENT"
				component={PageSubscribersCurrentEdit}
				pageHeaderIcon={faUserEdit}
				breadcrumb={[
					{
						name: "Dashboard",
						link: "/dashboard",
					},
					{
						name: "Subscribers",
						link: "/subscribers/current",
					},
					{
						name: "Current",
						link: "/subscribers/current",
					},
					{
						name: "Edit Current",
						link: "/subscribers/current/edit",
					},
				]}
			/>

			<PrivateRoute
				exact
				path="/subscribers/deactivated"
				title="Subscribers"
				subtitle="DEACTIVATED"
				component={PageSubscribersDeactivated}
				pageHeaderIcon={faUsers}
				breadcrumb={[
					{
						name: "Dashboard",
						link: "/dashboard",
					},
					{
						name: "Subscribers",
						link: "/subscribers/deactivated",
					},
					{
						name: "Deactivated",
						link: "/subscribers/deactivated",
					},
				]}
			/>
			{/* end Subscribers */}

			{/* Notifications */}
			<PrivateRoute
				exact
				path="/notifications"
				title="Notifications"
				subtitle="ADD"
				component={PageNotifications}
				pageHeaderIcon={faBell}
				breadcrumb={[
					{
						name: "Dashboard",
						link: "/dashboard",
					},
					{
						name: "Notifications",
						link: "/notifications",
					},
					{
						name: "Add Notifications",
						link: "/notifications/add",
					},
				]}
			/>
			{/* end Notifications */}

			{/* Email Templates */}
			<PrivateRoute
				exact
				path="/email-templates"
				title="Templates"
				subtitle="EMAIL"
				component={PageEmailTemplates}
				pageHeaderIcon={faPaperPlane}
				breadcrumb={[
					{
						name: "Dashboard",
						link: "/dashboard",
					},
					{
						name: "Email Templates",
						link: "/email-templates",
					},
				]}
			/>
			{/* end Email Templates */}

			{/* account-type */}
			<PrivateRoute
				exact
				path="/account-type/caregivers"
				title="Account Type"
				subtitle="VIEW/ADD/EDIT CAREGIVERS"
				component={PageAccountType}
				pageHeaderIcon={faUserCog}
				breadcrumb={[
					{
						name: "Dashboard",
						link: "/dashboard",
					},
					{
						name: "Account Type",
						link: "/account-type/caregivers",
					},
					{
						name: "Caregivers",
						link: "/account-type/caregivers",
					},
				]}
			/>
			<PrivateRoute
				exact
				path="/account-type/careprofessional"
				title="Account Type"
				subtitle="VIEW/ADD/EDIT CARE PROFESSIONAL"
				component={PageAccountType}
				pageHeaderIcon={faUserCog}
				breadcrumb={[
					{
						name: "Dashboard",
						link: "/dashboard",
					},
					{
						name: "Account Type",
						link: "/account-type/careprofessional",
					},
					{
						name: "Care Professional",
						link: "/account-type/careprofessional",
					},
				]}
			/>
			{/* end account-type */}

			{/* references  add here */}

			{/* end references add here  */}

			<PrivateRoute
				exact
				path="/viewas"
				component={ViewAs}
				permission="View As"
				title="USER"
				subtitle="View As"
				pageHeaderIcon={faBullhorn}
				breadcrumbs={[
					{
						name: "View As",
						link: null,
					},
				]}
			/>

			{/* this should always in the bottom */}

			<Route exact path="/*" component={Error404} />
			<Route exact path="/500" component={Error500} />
		</Switch>
	);
}
