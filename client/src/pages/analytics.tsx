import { StatCard } from "@/components/stat-card";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { DollarSign, TrendingUp, Package, Users } from "lucide-react";

//todo: remove mock functionality
const topProducts = [
  { name: "Cappuccino", sold: 145, revenue: 652.50 },
  { name: "Latte", sold: 128, revenue: 640.00 },
  { name: "Espresso", sold: 98, revenue: 343.00 },
  { name: "Croissant", sold: 76, revenue: 266.00 },
];

export default function Analytics() {
  return (
    <div className="p-6 space-y-6">
      <div>
        <h1 className="text-3xl font-bold">Analytics & Reports</h1>
        <p className="text-muted-foreground">Business insights and performance metrics</p>
      </div>

      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <StatCard
          title="Total Revenue"
          value="$12,847"
          icon={DollarSign}
          trend={{ value: 15.3, isPositive: true }}
          subtitle="this month"
        />
        <StatCard
          title="Total Orders"
          value="487"
          icon={TrendingUp}
          trend={{ value: 8.1, isPositive: true }}
          subtitle="this month"
        />
        <StatCard
          title="Items Sold"
          value="1,247"
          icon={Package}
          trend={{ value: 12.4, isPositive: true }}
          subtitle="this month"
        />
        <StatCard
          title="Active Employees"
          value="8"
          icon={Users}
          subtitle="on shift today"
        />
      </div>

      <div className="grid gap-6 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Top Selling Products</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {topProducts.map((product, idx) => (
                <div key={idx} className="flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <div className="flex items-center justify-center w-8 h-8 rounded-full bg-primary text-primary-foreground font-semibold text-sm">
                      {idx + 1}
                    </div>
                    <div>
                      <p className="font-medium" data-testid={`top-product-${idx}`}>{product.name}</p>
                      <p className="text-sm text-muted-foreground">{product.sold} sold</p>
                    </div>
                  </div>
                  <span className="font-bold">${product.revenue.toFixed(2)}</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Sales Overview</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div className="flex items-center justify-between p-4 rounded-lg bg-muted">
                <div>
                  <p className="text-sm text-muted-foreground">This Week</p>
                  <p className="text-2xl font-bold">$3,247</p>
                </div>
                <TrendingUp className="h-8 w-8 text-chart-2" />
              </div>
              <div className="flex items-center justify-between p-4 rounded-lg bg-muted">
                <div>
                  <p className="text-sm text-muted-foreground">This Month</p>
                  <p className="text-2xl font-bold">$12,847</p>
                </div>
                <DollarSign className="h-8 w-8 text-primary" />
              </div>
              <div className="flex items-center justify-between p-4 rounded-lg bg-muted">
                <div>
                  <p className="text-sm text-muted-foreground">Average Order Value</p>
                  <p className="text-2xl font-bold">$26.38</p>
                </div>
                <Package className="h-8 w-8 text-chart-3" />
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
