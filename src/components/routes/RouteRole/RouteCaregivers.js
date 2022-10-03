import React from "react";
import { Route, Switch } from "react-router-dom";

import { faHome } from "@fortawesome/pro-regular-svg-icons";

/** template */
import PrivateRoute from "../PrivateRoute";

import Error404 from "../../views/errors/Error404";
import Error500 from "../../views/errors/Error500";

import PageDashboard from "../../views/private/PageCaregiver/PageDashboard/PageDashboard";

export default function RouteCaregivers() {
	return (
		<Switch>
			<PrivateRoute
				exact
				path="/dashboard"
				title="Dashboard"
				subtitle="CANCER CAREGIVER"
				component={PageDashboard}
				pageHeaderIcon={faHome}
				breadcrumb={[
					{
						name: "Dashboard",
						link: "/dashboard",
					},
				]}
			/>

			<Route exact path="/*" component={Error404} />
			<Route exact path="/500" component={Error500} />
		</Switch>
	);
}
