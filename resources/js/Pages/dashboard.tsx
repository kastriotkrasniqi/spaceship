import AuthenticatedLayout from "@/layouts/authenticated-layout";
import { Head } from "@inertiajs/react";

export default function Dashboard({ starships }) {
    console.log(starships);
    return (
        <AuthenticatedLayout
            header="Dashboard"
        >
            <Head title="Dashboard" />

            <div className="flex flex-1 flex-col gap-4 h-full">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="aspect-video rounded-xl bg-muted/50" >
                    {/* // display starships here  */}
                    {starships.data.map((starship) => (
                        <div key={starship.id}>
                            {starship.name} - {starship.status}

                        </div>
                    ))}

                    </div>
                    <div className="aspect-video rounded-xl bg-muted/50" />
                    <div className="aspect-video rounded-xl bg-muted/50" />
                </div>
                <div className="flex-1 rounded-xl bg-muted/50 h-full" />
            </div>
        </AuthenticatedLayout>
    );
}
