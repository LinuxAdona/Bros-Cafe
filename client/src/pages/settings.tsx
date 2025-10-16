import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Switch } from "@/components/ui/switch";

export default function Settings() {
  return (
    <div className="p-6 space-y-6">
      <div>
        <h1 className="text-3xl font-bold">Settings</h1>
        <p className="text-muted-foreground">Manage your application settings</p>
      </div>

      <div className="grid gap-6 md:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Business Information</CardTitle>
            <CardDescription>Update your cafe details</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="cafe-name">Cafe Name</Label>
              <Input id="cafe-name" defaultValue="Bro's Cafe" data-testid="input-cafe-name" />
            </div>
            <div className="space-y-2">
              <Label htmlFor="cafe-address">Address</Label>
              <Input id="cafe-address" placeholder="123 Coffee Street" data-testid="input-cafe-address" />
            </div>
            <div className="space-y-2">
              <Label htmlFor="cafe-phone">Phone Number</Label>
              <Input id="cafe-phone" placeholder="+1 234 567 8900" data-testid="input-cafe-phone" />
            </div>
            <Button className="w-full" data-testid="button-save-business">Save Changes</Button>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Notifications</CardTitle>
            <CardDescription>Configure notification preferences</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>Low Stock Alerts</Label>
                <p className="text-sm text-muted-foreground">Get notified when items are running low</p>
              </div>
              <Switch defaultChecked data-testid="switch-low-stock" />
            </div>
            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>New Orders</Label>
                <p className="text-sm text-muted-foreground">Receive alerts for new orders</p>
              </div>
              <Switch defaultChecked data-testid="switch-new-orders" />
            </div>
            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>Daily Reports</Label>
                <p className="text-sm text-muted-foreground">Get daily sales summaries</p>
              </div>
              <Switch data-testid="switch-daily-reports" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Online Ordering</CardTitle>
            <CardDescription>Configure online ordering settings</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>Enable Online Orders</Label>
                <p className="text-sm text-muted-foreground">Allow customers to order online</p>
              </div>
              <Switch defaultChecked data-testid="switch-online-orders" />
            </div>
            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <Label>Delivery Service</Label>
                <p className="text-sm text-muted-foreground">Offer delivery to customers</p>
              </div>
              <Switch defaultChecked data-testid="switch-delivery" />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Account</CardTitle>
            <CardDescription>Manage your account settings</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="email">Email</Label>
              <Input id="email" type="email" placeholder="admin@broscafe.com" data-testid="input-email" />
            </div>
            <div className="space-y-2">
              <Label htmlFor="current-password">Current Password</Label>
              <Input id="current-password" type="password" data-testid="input-current-password" />
            </div>
            <div className="space-y-2">
              <Label htmlFor="new-password">New Password</Label>
              <Input id="new-password" type="password" data-testid="input-new-password" />
            </div>
            <Button className="w-full" data-testid="button-update-password">Update Password</Button>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
